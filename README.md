
# 📝 Task Manager — Full-Stack Application

A full-stack Task Management application built using **React, Core PHP, and MySQL**. The application provides task CRUD operations, JWT authentication, role-based authorization, status filtering, pagination, and AI-assisted task-priority suggestions using Google Gemini.

## 📌 Features

### Task 1 — Full-Stack CRUD
- Create, view, update, and delete tasks.
- Manage task title, description, status, priority, due date, and owner.
- Filter tasks by status.
- Paginate task-list results.
- Display loading indicators and error messages.
- Validate inputs on both the frontend and backend.

### Task 2 — Authentication & Authorization
- User registration and login.
- Secure password hashing and verification.
- JWT-based authentication.
- Protected task API endpoints.
- Role-based access control for `admin` and `user`.
- Regular users can manage their own tasks.
- Administrators can access tasks across users, subject to server-side authorization.
- Logout and JWT token revocation handling.

### Task 3 — Database Design & Query Optimization
- MySQL relational database schema.
- Primary keys, foreign keys, constraints, and indexes.
- Task filtering, pagination, and owner-related query design.
- Database query optimization documentation and execution-plan analysis.

📄 **Database Design Documentation:**  
[Task 3 — Database Design & Query Optimization](https://docs.google.com/document/d/19iuYm7O32XMTw_8UzNCBFPnxnTQcBM0Ghc-uMsu6Zw4/edit?usp=sharing)

### Task 6 — AI-Powered Priority Suggestions
- Integrates the Google Gemini API to suggest task priorities based on task details.
- Allows users to review suggested priorities before saving.
- Requires a valid Gemini API key configured on the backend.

## 🛠️ Technology Stack

| Component | Technology |
|---|---|
| Frontend | React, Vite, JavaScript, CSS |
| Backend | Core PHP, REST API |
| Database | MySQL |
| Authentication | JSON Web Tokens (JWT) |
| AI Integration | Google Gemini API |
| Local Development | Laragon, Node.js, npm |
| Version Control | Git, GitHub |

## 📂 Project Structure

```text
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
```

*Note: This is a simplified overview of the project structure.*

## ⚙️ Prerequisites

Ensure the following are installed:

- PHP with the required extensions.
- MySQL.
- [Laragon](https://laragon.org/) or another PHP/MySQL environment.
- [Node.js and npm](https://nodejs.org/).
- [Git](https://git-scm.com/).

## 🚀 Installation & Setup

### 1. Clone the Repository

```bash
git clone <YOUR_GITHUB_REPOSITORY_URL>
cd task_manager
```

Replace `<YOUR_GITHUB_REPOSITORY_URL>` with your actual repository URL.

### 2. Configure the Backend

1. Place the project inside the Laragon web root:

   ```text
   C:\laragon\www\task_manager
   ```

2. Start **Apache** and **MySQL** from Laragon.

3. Create the database and execute the SQL schema provided in the repository. If the SQL script already creates the database, execute that script directly.

   ```sql
   CREATE DATABASE task_manager;
   ```

4. Configure the database credentials using the configuration method expected by your backend.

Example environment settings, if supported by your configuration:

```env
DB_HOST=127.0.0.1
DB_NAME=task_manager
DB_USER=your_database_user
DB_PASSWORD=your_database_password
```

5. Configure the JWT signing secret using the backend's expected configuration.

6. To enable AI priority suggestions, configure your Gemini API key in the backend environment configuration.

**Security:** Never commit database credentials, JWT secrets, or API keys to Git.

### 3. Configure the Frontend

Open a terminal in the frontend directory:

```bash
cd frontend
npm install
```

Create or update `frontend/.env`:

```env
VITE_API_BASE_URL=http://localhost/task_manager/backend/api
```

Ensure the URL matches your local Apache configuration and the actual location of the PHP API.

### 4. Run the Application

Start Apache and MySQL through Laragon.

In your terminal, run:

```bash
cd frontend
npm run dev
```

Open the URL displayed by Vite, usually:

**http://localhost:5173/**

Register an account, log in, and start managing tasks.

> Restart the Vite development server whenever you change frontend environment variables.

## 🔑 Authentication & Authorization

The application uses JWT-based authentication to protect API endpoints.

- Users authenticate through the login endpoint.
- Protected requests include the JWT token.
- The backend validates authentication before allowing protected operations.
- Regular users are restricted to their own tasks.
- Administrators can access tasks across users when authorized by the backend.
- Logout uses the application's token-revocation mechanism.

Authorization is enforced by the backend; hiding frontend buttons alone is not sufficient to protect resources.

## 🗃️ Database Documentation

The database design document covers the schema, relationships, indexing strategy, example SQL queries, and query execution-plan analysis.

👉 [View Task 3 Documentation](https://docs.google.com/document/d/19iuYm7O32XMTw_8UzNCBFPnxnTQcBM0Ghc-uMsu6Zw4/edit?usp=sharing)

## 🔒 Security Considerations

- Passwords are stored using secure password hashing.
- Prepared statements are used for database operations.
- Task data is validated on the server.
- Task ownership and role permissions are checked server-side.
- Sensitive configuration values are excluded from version control.
- Authentication and authorization failures return appropriate HTTP status codes.

## 📋 Assignment Coverage

| Task | Description | Status |
|---|---|---|
| Task 1 | Full-Stack CRUD App | Completed |
| Task 2 | Authentication & Authorization | Completed |
| Task 3 | Database Design & Query Optimization | Documentation linked above |
| Task 6 | AI Priority Suggestions | Implemented; requires Gemini configuration |


