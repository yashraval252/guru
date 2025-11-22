# Har Mahadev Voice Entry Application

## Project Overview

This is a PHP-based web application that allows users to register, log in, and add entries with a title and date. The application integrates AI-powered voice command functionality, enabling users to add entries by speaking in Hindi, Gujarati, or English. The voice input is processed via speech-to-text and natural language understanding (NLU) APIs to extract entry details automatically.

## Features

- User registration and login with secure password hashing
- User dashboard to add and view entries
- AI voice command interface that listens for the wake word "Har Mahadev"
- Speech-to-text transcription using OpenAI Whisper API (or optionally Google Speech-to-Text)
- NLU processing to extract entry title and date from transcribed text
- AJAX-based entry addition without page reload
- Responsive UI with client-side form validation
- Secure session management and input validation
- Dockerized environment with PHP-Apache and MySQL services
- PHPUnit tests for auth, entries API, and voice command processing

## Prerequisites

- Docker and Docker Compose installed on your machine
- PHP 7.4 or higher (if running outside Docker)
- MySQL 5.7+ or compatible (if running outside Docker)
- Composer (for PHP dependencies)
- OpenAI API key (for speech-to-text)
- Google Speech-to-Text API key (optional alternative)

## Installation Steps

1. **Clone the repository:**

    git clone https://your-repo-url.git
    cd your-repo-folder

2. **Copy environment variables template:**

    cp .env.example .env

3. **Edit `.env` file:**

   Fill in your database credentials and API keys:

       DB_HOST=localhost
       DB_NAME=harmahadev_db
       DB_USER=your_db_user
       DB_PASS=your_db_password

       OPENAI_API_KEY=your_openai_api_key_here
       GOOGLE_SPEECH_API_KEY=your_google_speech_api_key_here

4. **Set up the database:**

   - If using Docker (recommended), run:

         docker-compose up -d

   - After MySQL container is running, apply schema:

         docker exec -i <mysql_container_name> mysql -uroot -prootpassword harmahadev_db < database/schema.sql

   - If running manually, create database and tables with:

         mysql -u your_db_user -p your_db_password < database/schema.sql

5. **Install PHP dependencies (outside Docker):**

       composer install

6. **Configure web server (if not using Docker):**

   - Serve the project root over Apache or PHP built-in server:

         php -S localhost:8000

7. **Access the application:**

   - Open your browser and navigate to `http://localhost` (Docker) or `http://localhost:8000` (PHP built-in).

## Configuration Instructions

- **Environment Variables**

  The application uses environment variables from `.env` file. Sensitive keys like API keys and database credentials must be set here.

- **Session Settings**

  Session cookie settings can be configured via `.env`. Defaults are generally secure for local development.

## Running the Application

- **Registration**

  Navigate to the registration page and create a new account.

- **Login**

  Use your registered email and password to log in.

- **Dashboard**

  Add entries manually via the form or click the "Voice Command" button and say "Har Mahadev" followed by your entry details in Hindi, Gujarati, or English.

- **Logout**

  Use the logout link in the dashboard header.

## Usage Examples

- Voice command example:

      "Har Mahadev add entry Meeting on 2024-06-15"

- The app will detect the wake word, record your speech, transcribe it, extract the title "Meeting" and date "2024-06-15", then add the entry automatically.

## API Details

- **POST /api/entries.php**

  Adds a new entry. JSON body:

      {
          "title": "string",
          "date": "YYYY-MM-DD"
      }

- **GET /api/entries.php**

  Fetches all entries for the logged-in user.

- **POST /api/voice_process.php**

  Accepts audio file upload (multipart/form-data) with field `audio`. Returns JSON transcription.

- **POST /api/nlu_process.php**

  Accepts JSON with `text` field. Returns extracted `title` and `date`.

## Troubleshooting

- **Database connection errors**

  Verify your `.env` settings for DB credentials and ensure MySQL is running.

- **Session issues**

  Ensure PHP session settings and cookie parameters are correctly set in `.env`.

- **Voice command not working**

  Confirm your browser supports Web Speech API and microphone access is allowed.

- **API errors**

  Check API keys for OpenAI and Google Speech-to-Text are valid and have sufficient quota.

- **Docker issues**

  Ensure Docker daemon is running and no port conflicts exist on 80 or 3306.

## Project Structure Explanation

- `index.php`: Login page and entry point.
- `registration.php`: User registration page.
- `dashboard.php`: User dashboard after login.
- `auth/`: Authentication related backend scripts.
- `api/`: API endpoints for entries, voice processing, and NLU.
- `classes/`: PHP classes including Database connection.
- `config.php`: Configuration and session initialization.
- `js/`: Frontend JavaScript files.
- `styles/`: CSS styles.
- `database/schema.sql`: MySQL database schema.
- `tests/`: PHPUnit test cases.
- `Dockerfile`: Docker image definition for PHP Apache.
- `docker-compose.yml`: Docker Compose configuration.
- `.env.example`: Environment variable template.
- `composer.json`: PHP dependencies and autoload config.

## License

MIT License

---

Enjoy using the Har Mahadev Voice Entry Application!
