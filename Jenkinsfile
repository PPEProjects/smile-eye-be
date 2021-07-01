pipeline {
  agent any
  stages {
    stage('Build') {
      steps {
        echo 'Building'
        sh 'composer install'
        sh 'php artisan migrate'
      }
    }

    stage('Deploy') {
          steps {
            echo 'deploing'
          }
    }

  }
  post {
    always {
        echo 'always four time'
    }
    success {
        echo 'success'
    }
    failure {
          echo 'failure'
    }

  }
}
