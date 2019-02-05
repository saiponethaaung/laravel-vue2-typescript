pipeline {
  agent any
  stages {
    stage('Build') {
      parallel {
        stage('Build') {
          steps {
            sh 'docker-compose up -d'
            sh 'docker ps'
            sh 'docker-compose stop'
            sh 'docker-compose down'
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