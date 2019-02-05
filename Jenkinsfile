pipeline {
    agent any

    stages {
      stage('Docker Build') {
          steps {
              sh 'docker-compose down'
              sh 'docker-compose up -d'
          }
      }
      
      stage('Testing') {
          steps {
              sh 'docker exec test2_php_1 bash -c \'composer install && cp .env.example .env && php artisan key:generate && vendor/bin/phpunit\''
              sh 'docker cp test2_php_1:/var/www/html/public/test-report /var/www/test2-report'
              junit 'public/test-report/logfile.xml'
              publishHTML([allowMissing: false, alwaysLinkToLastBuild: false, keepAll: false, reportDir: 'public/test-report/', reportFiles: 'index.html', reportName: 'HTML Report', reportTitles: ''])
          }
      }

      stage('Docker Destory') {
          steps {
              sh 'docker-compose stop'
              sh 'docker-compose down'
          }
      }
          
      stage('Publish Development') {
          steps {
              echo 'in progress'
          }
      }
  }

    post {
        always {
            echo 'This will always run'
        }
        success {
            emailextrecipients([developers(), requestor(), upstreamDevelopers()])
            echo 'This will run only if successful'
        }
        failure {
            emailextrecipients([brokenTestsSuspects(), culprits(), developers(), requestor(), brokenBuildSuspects(), upstreamDevelopers()])
            echo 'This will run only if failed'
        }
        unstable {
            echo 'This will run only if the run was marked as unstable'
        }
        changed {
            echo 'This will run only if the state of the Pipeline has changed'
            echo 'For example, if the Pipeline was previously failing but is now successful'
        }
    }
}