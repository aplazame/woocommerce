library "aplazame-shared-library"
deployToPro = false

pipeline {
  agent {
    kubernetes {
      yamlFile "/jenkins/php.yaml"
    }
  }
  environment {
    FOLDER = "dist"
    foldersCache = '"vendor/"'
    GITHUB_TOKEN = credentials('gh-releases-token')
  }
  options {
    disableConcurrentBuilds()
    ansiColor('xterm')
  }
  stages {
    stage('Test Sonarqube') {
      when {
        not {
          tag "*"
        }
      }
      agent {
        kubernetes {
          yamlFile "/jenkins/jenkins-sonar.yaml"
        }
      }
      environment {
        SONAR_TEST = credentials('SONAR_TEST')
        WORDPRESS_USERNAME = credentials('WORDPRESS_USERNAME')
        WORDPRESS_PASSWORD = credentials('WORDPRESS_PASSWORD')
        CODE_SOURCE_DEFAULT = "plugin"
        GITHUB_TOKEN = credentials('gh-releases-token')
      }
      steps {
        scmSkip()
        container('sonar') {
        sonarScan(SONAR_TEST,CODE_SOURCE_DEFAULT)
        }
      }
    }
    stage("Get cache") {
      when {
        not {
          tag "*"
        }
      }
      steps {
        script {
          HASH = sh(script: 'md5sum composer.json | awk \'{print \$1}\'', returnStdout: true).trim()
          CACHE_KEY = 'v1-dependencies-' + HASH

          container('php') {
            sh """
              load-config
              export AWS_PROFILE=AplazameSharedServices
              set -e
              aws s3 cp --quiet s3://aplazameshared-jenkins-cache/Aplazame-Backend/woocommerce/${CACHE_KEY} cache.tar.gz || exit 0
              [ -f cache.tar.gz ] && tar -xf cache.tar.gz
            """
            //loadCache(CACHE_KEY)
          }
        }
      }
    }
    stage("Composer Install") {
      when {
        not {
          tag "*"
        }
      }
      steps {  
          container('php') {
            sh """
              composer install -n --prefer-dist
            """
          }
      }
    }
    stage("Upload Cache") {
      when {
        not {
          tag "*"
        }
      }
      steps {  
        container('php') {
        sh """
            load-config
            export AWS_PROFILE=AplazameSharedServices
            set -e
            MATCHES=\$(aws s3 ls s3://aplazameshared-jenkins-cache/Aplazame-Backend/woocommerce/${CACHE_KEY} | wc -l)
            [ "\$MATCHES" = "0" ] && [ ! -f cache.tar.gz ] && tar -czf cache.tar.gz vendor/ && aws s3 cp --quiet cache.tar.gz s3://aplazameshared-jenkins-cache/Aplazame-Backend/woocommerce/${CACHE_KEY}
            exit 0
        """
          //saveCache(CACHE_KEY,["${foldersCache}"])
        }
      }
    }
    stage("Check Syntax") {
      when {
        not {
          tag "*"
        }
      }
      steps {  
        container('php') {
          sh """
            make syntax.checker
          """
        }
      }
    }
    stage("CS Style") {
      when {
        not {
          tag "*"
        }
      }
      steps {  
        container('php') {
          sh """
            make style
          """
        }
      }
    }
    stage("Create bundle") {
      when {
        branch 'master'
      }
      steps {  
        container('php') {
          sh """
            make zip
          """
        }
      }
    }
    stage("Deploy to S3") {
      when {
        branch 'master'
      }
      steps {  
        scmSkip()

        timeout(time: 15, unit: "MINUTES") {
          script {
            slackSend failOnError: true, color: '#8000FF', channel: '#backend-pipelines', message: "You need :hand: intervention in ${currentBuild.fullDisplayName} (<${env.BUILD_URL}console|Open here>)", username: "Jenkins CI"
            input id: 'ReleaseApproval', message: 'Deploy to S3?', ok: 'Yes'
          }
        }
        container('php') {
          sh """
            echo "Deploy to S3"
            load-config
            export AWS_PROFILE=Aplazame
            aws s3 cp --acl public-read aplazame.latest.zip s3://aplazame/modules/woocommerce/
          """
        }
      }
    }
    stage('Deploy to Wordpress and Create Release') {
      when {  
        branch 'master'
      }
      steps {
        script {
          try {
            timeout(time: 10, unit: "MINUTES") {
              slackSend failOnError: true, color: '#8000FF', channel: '#backend-pipelines', message: "You need :hand: intervention in ${currentBuild.fullDisplayName} (<${env.BUILD_URL}console|Open here>)", username: "Jenkins CI"
              deployToPro = input(id: 'deployToPro', message: 'Deploy to Wordpress and Creatate Tag?',
                parameters: [
                  booleanParam(name: 'deploy', description: 'Deploy to Wordpress and Creatate Tag?', defaultValue: false)
              ])
            }
          } catch (err) {
            currentBuild.result = "SUCCESS"
          }
        }
      }
    }
    stage("Create Release") {
      when {
        beforeAgent true
        allOf {
          expression { deployToPro }
          branch 'master'
        }
      }
      steps {
        container('php') {
          sh """
            echo "***************Create Release***************"
            export APP_VERSION="\$(cat Makefile | grep 'version ?=' | cut -d '=' -f2 | cut -c2-)"
            echo \$APP_VERSION
            echo "\$APP_VERSION" > APP_VERSION.tmp
            gh release create \$APP_VERSION --notes "Release created by Jenkins.<br />Build: $BUILD_TAG;$BUILD_URL&gt;"
          """
        }
      }
    }
    stage("Deploy to Wordpress") {
      when {
        beforeAgent true
        allOf {
          expression { deployToPro }
          branch 'master'
        }
      }
      environment {
        WORDPRESS_USERNAME = credentials('WORDPRESS_USERNAME')
        WORDPRESS_PASSWORD = credentials('WORDPRESS_PASSWORD')
      }
      steps {
        container('php') {
          sh """
            echo "***************Clone wordpress repository***************"
            svn co https://plugins.svn.wordpress.org/aplazame svn
          """
          sh """
            echo "****************Sync assets******************************"
            rsync -r -p --delete assets/ svn/assets
            svn add --force svn/assets
          """
          sh """
            echo "****************Sync trunk******************************"
            rsync -r -p --delete plugin/ svn/trunk
            svn add --force svn/trunk
          """
          sh """
            echo "****************Delete unused files******************************"
            for i in \$(svn status svn | grep \\! | awk '{print \$2}'); do svn delete \$i; done
          """
          sh """
            echo "****************Tag Release******************************"
            export APP_VERSION="\$(cat APP_VERSION.tmp)"
            echo \$APP_VERSION
            svn cp svn/trunk svn/tags/\$APP_VERSION
          """
          sh """
            echo "****************Commit to Wordpress******************************"
            export APP_VERSION="\$(cat APP_VERSION.tmp)"
            echo \$APP_VERSION
            svn ci --no-auth-cache --username ${WORDPRESS_USERNAME} --password ${WORDPRESS_PASSWORD} svn -m "tagging version \$APP_VERSION"
          """
        }
      }
    }
  }
}