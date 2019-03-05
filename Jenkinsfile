pipeline {
    agent any

    stages {
        stage('Set docker prefix') {
            steps {
                script {
                    def path = (pwd().toLowerCase()).tokenize('/').last();
                    env.dockerPrefix = path.replaceAll('-', '');
                    env.dockerPrefix = env.dockerPrefix.replaceAll('_', '');
                    env.dockerPrefix = env.dockerPrefix.replaceAll(' ', '');
                }
            }
        }

        stage('Docker Build') {
            steps {
                sh 'docker stop $(docker ps -aq)'
                sh 'docker-compose -f docker-compose-jenkins.yml up -d'
            }
        }
        
        stage('Testing') {
            steps {
                sh "docker exec ${env.dockerPrefix}_php_1 bash -c 'composer install && cp .env.example .env && php artisan key:generate && vendor/bin/phpunit'"
                sh "docker cp ${env.dockerPrefix}_php_1:/var/www/html/public/test-report /var/www/chatbot-saiapple-v1-report"
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
            junit 'public/test-report/logfile.xml'
            publishHTML([allowMissing: false, alwaysLinkToLastBuild: false, keepAll: false, reportDir: 'public/test-report/', reportFiles: 'index.html', reportName: 'Code Coverage Report', reportTitles: ''])
            sh "docker exec ${env.dockerPrefix}_php_1 bash -c 'rm -rf vendor'"
            sh "docker exec ${env.dockerPrefix}_php_1 bash -c 'rm -rf public/test-report'"
            sh 'docker-compose stop'
            sh 'docker-compose down'
        }
        success {
            emailextrecipients([developers(), requestor(), upstreamDevelopers()])
            emailext (
                subject: "SUCCESSFUL: Job '${env.JOB_NAME} [${env.BUILD_NUMBER}]'",
                body: """<p>SUCCESSFUL: Job '${env.JOB_NAME} [${env.BUILD_NUMBER}]':</p>
                    <p>Check console output at &QUOT;<a href='${env.BUILD_URL}'>${env.JOB_NAME} [${env.BUILD_NUMBER}]</a>&QUOT;</p>""",
                recipientProviders: [[$class: 'DevelopersRecipientProvider']]
            )
        }
        failure {
            emailextrecipients([brokenTestsSuspects(), culprits(), developers(), requestor(), brokenBuildSuspects(), upstreamDevelopers()])
            emailext (
                subject: "FAILED: Job '${env.JOB_NAME} [${env.BUILD_NUMBER}]'",
                body: """<p>FAILED: Job '${env.JOB_NAME} [${env.BUILD_NUMBER}]':</p>
                    <p>Check console output at &QUOT;<a href='${env.BUILD_URL}'>${env.JOB_NAME} [${env.BUILD_NUMBER}]</a>&QUOT;</p>""",
                recipientProviders: [[$class: 'DevelopersRecipientProvider']]
            )
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