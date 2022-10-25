library "aplazame-shared-library"

pipeline {
  agent {
    kubernetes {
      yamlFile "/jenkins/php.yaml"
    }
  }

  stages {
    stage("Deploy to Wordpress") {
      environment {
        WORDPRESS_USERNAME = credentials('WORDPRESS_USERNAME')
        WORDPRESS_PASSWORD = credentials('WORDPRESS_PASSWORD')
      }
      steps {
        container('php') {
          sh """
            echo "****************Commit to Wordpress******************************"
            echo ${WORDPRESS_USERNAME}
            echo ${WORDPRESS_PASSWORD}
            #svn ci --no-auth-cache --username ${WORDPRESS_USERNAME} --password ${WORDPRESS_PASSWORD} svn -m "tagging version \$APP_VERSION"
          """
        }
      }
    }
  }
}