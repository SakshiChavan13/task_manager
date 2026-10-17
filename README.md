
Task management

Features

Task 1 — Full-Stack CRUD

Create, view, update, and delete tasks.

Store task title, description, status, priority, due date, and owner.

Filter tasks by status.

Paginate task-list results.

Display loading indicators and error messages.

Validate inputs on both the frontend and backend.

Task 2 — Authentication and Authorization

User registration and login.

Secure password hashing and verification.

Token-based authentication using JWT.

Protected task API endpoints.

Role-based access for administrators and regular users.

Users can manage their own tasks; administrators can access tasks across users, subject to server-side authorization.

Logout and token handling.

TASK 3: 

https://docs.google.com/document/d/19iuYm7O32XMTw_8UzNCBFPnxnTQcBM0Ghc-uMsu6Zw4/edit?usp=sharing


TASK 6: 

AI-assisted task-priority suggestions using the Gemini API, where configured.

Technology Stack:

Frontend:

React, Vite, JavaScript, CSS

Backend:

Core PHP, REST API

Database:

MySQL

Authentication:

JWT

AI integration:

Google Gemini API

Local development:

Laragon, Node.js, npm

Version control:

Git and GitHub

Project Structure:

task_manager/
├── backend/
│   ├── api/
│   │   ├── auth/
│   │   └── tasks/
│   ├── config/
│   └── ...
├── frontend/
│   ├── src/
│   │   ├── components/
│   │   ├── api.js
│   │   └── ...
│   ├── .env
│   └── package.json
├── .gitignore
└── README.md



Install the following before running the project:

PHP with the required extensions enabled.

MySQL.

Laragon or another local PHP/MySQL environment.

Node.js and npm.

Git.

Installation and Setup:

1. Clone the repository

git clone <YOUR_GITHUB_REPOSITORY_URL>
cd task_manager

2. Configure the backend


Start Apache and MySQL from Laragon.

Create the project database in MySQL. Use the database name configured by your backend.

Execute the database setup script included in the repository. 

Configure the backend database credentials and other required settings in the backend configuration or environment file used by the project.

Example settings:
DB_HOST=127.0.0.1
DB_NAME=your_database_name
DB_USER=your_database_user
DB_PASSWORD=your_database_password

Configure the JWT secret according to the backend's expected configuration. 

For AI priority suggestions, configure the Gemini API key in the backend environment configuration.

3. Configure the frontend

Open a terminal in the frontend directory:

cd frontend
npm install

Create or update frontend/.env:

VITE_API_BASE_URL=http://localhost/task_manager/backend/api

Ensure this URL matches the location of the PHP backend on your local server.

4. Start the application

Start Apache and MySQL in Laragon.

Then run the React development server:

cd frontend
npm run dev

Open the local URL printed by Vite, usually:

http://localhost:5173/

Register a user, sign in, and test the task-management features.

