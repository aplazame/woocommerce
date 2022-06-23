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

      steps {  
        container('php') {
          sh """
            make syntax.checker
          """
        }
      }
    }
    stage("CS Style") {

      steps {  
        container('php') {
          sh """
            make style
          """
        }
      }
    }
    stage("Create bundle") {

      steps {  
        container('php') {
          sh """
            make zip
          """
        }
      }
    }

    stage("Create Release") {

      steps {
        container('php') {
          sh """
            echo "***************Create Release***************"
            export APP_VERSION="\$(cat Makefile | grep 'version ?=' | cut -d '=' -f2)"
            echo \$APP_VERSION
            echo "\$APP_VERSION" > APP_VERSION.tmp
            
          """
        }
      }
    }
    stage("Deploy to Wordpress") {

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
          """
        }
      }
    }
  }
}