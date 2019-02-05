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
            sh 'ls'
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