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
    stage("Create Release") {
      steps {
        container('php') {
          sh """
            echo "***************Create Release***************"

            gh release create v0.0.0.1-dirty --notes "Release created by Jenkins.<br />Build: $BUILD_TAG;$BUILD_URL&gt;"
          """
        }
      }
    }
  }
}