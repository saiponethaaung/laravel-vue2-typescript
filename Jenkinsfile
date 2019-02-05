pipeline {
  agent any
  stages {
    stage('Build') {
      parallel {
        stage('Build') {
          steps {
            echo 'Hello'
          }
        }
        stage('') {
          steps {
            echo 'World'
          }
        }
      }
    }
    stage('complete') {
      steps {
        echo 'end'
      }
    }
  }
}