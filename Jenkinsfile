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
        CODE_SOURCE_DEFAULT = "plugin"
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
            loadCache(CACHE_KEY)
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
          saveCache(CACHE_KEY,["${foldersCache}"])
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
           #aws s3 cp --acl public-read aplazame.latest.zip s3://aplazame/modules/woocommerce/
          """
        }
      }
    }
    stage('Deploy to Wordpress and Create Release') {
      //when {  
      //  branch 'master'
      //}
      steps {
        script {
          try {
            timeout(time: 1, unit: "MINUTES") {
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
      //    branch 'master'
        }
      }
      steps {
        container('php') {
          sh """
            echo "***************Create Release***************"
            export APP_VERSION="\$(cat Makefile | grep 'version ?=' | cut -d '=' -f2)"
            echo \$APP_VERSION
            echo "\$APP_VERSION" > APP_VERSION.tmp
            #gh release create \$APP_VERSION --notes "Release created by Jenkins.<br />Build: $BUILD_TAG;$BUILD_URL&gt;"
          """
        }
      }
    }
    stage("Deploy to Wordpress") {
      when {
        beforeAgent true
        allOf {
          expression { deployToPro }
      //    branch 'master'
        }
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
            #svn cp svn/trunk svn/tags/\$APP_VERSION:1
          """
          sh """
            echo "****************Commit to Wordpress******************************"
            echo \$APP_VERSION
            sleep 1h
            #svn ci --no-auth-cache --username $WP_USERNAME --password $WP_PASSWORD svn -m "tagging version \$APP_VERSION"
          """
        }
      }
    }
  }
}