# Reclamations Module

A full-stack PHP/MySQL module for handling user complaints.

## Structure
- **Model/**: Database connection and Complaint class.
- **Controller/**: endpoints for handling requests.
- **View/**: HTML/CSS/JS interfaces.
- **schema.sql**: Database schema.

## How to Run
1. Import `schema.sql` into your MySQL database.
2. Configure `Model/db.php` with your DB credentials.
3. Serve the `View` directory using a web server.
4. Access `form.html` to submit complaints and `dashboard.html` to manage them.
