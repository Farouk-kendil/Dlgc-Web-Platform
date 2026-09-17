# DLGC Web Platform

A business-oriented web platform for **DLGC**, designed to centralize access to company resources and provide a complete customer assistance workflow.

The project combines a responsive website entry point with a PHP/MySQL support module that allows customers to submit assistance requests and enables the DLGC team to manage, assign, follow and resolve them from a protected administration area.

## Main Features

### Website
- Responsive DLGC landing page
- Direct access to documentation, downloads, training resources and company services
- Links to hardware, software and ERP resources
- Contact information and social links
- Dedicated **Demande d’assistance** entry point

### Customer Assistance System
- Customer support request form
- Automatic ticket number generation
- Optional client code
- Company name, phone, subject and problem description
- Request workflow: **Nouveau → En cours → Résolu**
- Ticket assignment to employees
- Resolved request history
- Tracking of the employee who handled and resolved each ticket

### Administration
- Secure administrator login
- Two access levels: **Super Admin** and **Employee**
- Active ticket dashboard
- Ticket detail and status management
- Historical resolved requests
- Employee account management by the Super Admin
- Account activation/deactivation
- Password reset workflow

## Security & Backend Practices

The assistance module includes several practical security measures:

- PHP sessions with secure cookie settings
- Password hashing and verification
- CSRF protection
- PDO prepared statements
- Output escaping against XSS
- Role-based access control
- Active-account validation
- Session ID regeneration after login
- Basic anti-bot honeypot on the customer form

## Tech Stack

- **PHP 8**
- **MySQL / MariaDB**
- **PDO**
- **HTML5**
- **CSS3**
- **JavaScript**
- **Apache / XAMPP** for local development

## Project Structure

```text
Dlgc-Web-Platform/
├── README.md
└── dlgc66_assistance/
    ├── 111111.html
    ├── style.css
    ├── assets/
    └── assistance/
        ├── admin/
        ├── assets/
        ├── config/
        ├── database/
        ├── lib/
        ├── index.php
        ├── submit.php
        └── success.php
```

## Database

The repository includes a SQL backup of the assistance database:

```text
dlgc66_assistance/assistance/database/dlgc_assistance.sql
```

Main tables:

- `admins`
- `assistance_requests`

The database stores administrator accounts, roles, ticket information, assignment data and ticket history.

## Local Installation with XAMPP

1. Install and open **XAMPP**.
2. Start **Apache** and **MySQL**.
3. Copy the `dlgc66_assistance` folder into:

```text
C:\xampp\htdocs\
```

4. Open phpMyAdmin:

```text
http://localhost/phpmyadmin
```

5. Import:

```text
dlgc66_assistance/assistance/database/dlgc_assistance.sql
```

6. Open the website:

```text
http://localhost/dlgc66_assistance/111111.html
```

7. Open the customer assistance page:

```text
http://localhost/dlgc66_assistance/assistance/
```

8. Open the administration area:

```text
http://localhost/dlgc66_assistance/assistance/admin/
```

The default local database configuration uses XAMPP's standard MySQL setup and can be changed in:

```text
dlgc66_assistance/assistance/config/database.php
```

## Ticket Workflow

```text
Customer submits request
        ↓
      Nouveau
        ↓
Employee takes ownership
        ↓
     En cours
        ↓
Ticket resolved
        ↓
      Résolu
        ↓
    Historique
```

## Repository Scope

Some navigation links on the DLGC website point to documentation, installers, videos or internal company resources that are maintained separately on DLGC infrastructure and are intentionally not included in this repository.

The complete **customer assistance module**, administration workflow and database backup used for local development are included here.

## Purpose

This project demonstrates a practical business web application built around a real support workflow rather than a simple static interface. It combines public-facing content, database-backed forms, authentication, role management and administrative ticket processing in one system.

## Author

**Farouk Kendil**  
Full-Stack Web Developer

---

Built as part of the DLGC web platform project.
