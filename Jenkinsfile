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
    WORDPRESS_USERNAME = credentials('WORDPRESS_USERNAME')
    WORDPRESS_PASSWORD = credentials('WORDPRESS_PASSWORD')
  }
  options {
    disableConcurrentBuilds()
    ansiColor('xterm')
  }
  stages {

    stage("Create Release") {
      steps {
        container('php') {
          sh """
            echo "***************Create Release***************
            echo v3.6.3 > APP_VERSION.tmp
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
            #echo "****************Sync assets******************************"
            #rsync -r -p --delete assets/ svn/assets
            #svn add --force svn/assets
          """
          sh """
            #echo "****************Sync trunk******************************"
            #rsync -r -p --delete plugin/ svn/trunk
            #svn add --force svn/trunk
          """
          sh """
            #echo "****************Delete unused files******************************"
            #for i in \$(svn status svn | grep \\! | awk '{print \$2}'); do svn delete \$i; done
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
            echo ${WORDPRESS_USERNAME}
            echo ${WORDPRESS_PASSWORD}
            #svn ci --no-auth-cache --username ${WORDPRESS_USERNAME} --password ${WORDPRESS_PASSWORD} svn -m "tagging version \$APP_VERSION"
          """
        }
      }
    }
  }
}