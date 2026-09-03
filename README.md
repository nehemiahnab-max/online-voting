# Online Voting

A simple, secure online voting system built with PHP, JavaScript and CSS.

Description
-----------
Online Voting is a project that implements an election/voting system where authorized users can register, authenticate, create elections (admins), and cast votes. The system focuses on clarity, security best-practices, and auditability for small-scale deployments or as a learning project.

Key features
------------
- User registration & authentication
- Role-based access (voter, admin)
- Create/manage elections, positions, and candidates (admin)
- Cast votes with double-vote protection
- Vote tallying and results view (admin and public)
- Simple audit logging for vote events
- Lightweight, runs on a standard LAMP stack

Tech stack
----------
- PHP (backend) — primary language
- MySQL / MariaDB (database)
- JavaScript (client-side interactivity)
- CSS (styling)
- Optional: Composer for dependency management

Requirements
------------
- PHP 8.0+ (recommended)
- MySQL or MariaDB
- Web server (Apache / Nginx) or PHP built-in server for development
- Composer (optional, if using packages)

Quick start (development)
-------------------------
1. Clone the repo
   git clone https://github.com/nehemiahnab-max/online-voting.git
   cd online-voting

2. Install dependencies (if any)
   - If the project uses Composer:
     composer install

3. Configure the application
   - Copy example config file and update DB credentials:
     cp config.example.php config.php
     Edit `config.php` and set database credentials and other settings.
   - Or set environment variables if the project uses .env

4. Create the database
   - Create a database and import the schema:
     mysql -u root -p
     CREATE DATABASE online_voting;
     USE online_voting;
     SOURCE sql/schema.sql;  # if your repo includes a SQL schema

5. Seed initial data (optional)
   - Run the provided seed script or import a sample data file:
     php scripts/seed.php

6. Run the app
   - Using PHP built-in server (for dev):
     php -S localhost:8000 -t public
   - Or configure your web server (Apache/Nginx) to point to the project public directory

Setup checklist
---------------
- [ ] Set secure DB username/password
- [ ] Configure HTTPS in production
- [ ] Disable detailed error display in production (display_errors = Off)
- [ ] Set proper file and folder permissions for uploads/logs
- [ ] Configure email (password recovery, notifications) if used

Usage overview
--------------
- Admin:
  - Create elections, positions, and candidates
  - Open/close voting windows
  - View results & audit logs
- Voter:
  - Register and verify account (if verification is implemented)
  - Authenticate and cast vote during open voting window
  - View public election results after close

Security considerations
-----------------------
This project demonstrates core concepts but take care before using in production:
- Use HTTPS (TLS) for all traffic
- Protect against CSRF (use tokens on forms)
- Validate and sanitize all user input (server-side)
- Hash passwords with a secure algorithm (bcrypt / Argon2)
- Implement rate limiting / bot protections
- Audit logs: store immutable event logs for vote actions
- Prevent double voting: enforce one-vote-per-voter per election at DB level (unique constraints)
- Consider additional measures for large/critical elections: two-factor auth, voter verification, end-to-end verifiable voting protocols, third-party audits

Database notes
--------------
- Use transactions for vote submission to ensure atomicity (decrement/lock candidate tallies if using aggregated counters)
- Add a unique constraint on (election_id, voter_id) in the votes table
- Keep audit logs in a separate append-only table

Testing
-------
- Unit and integration tests (if present) can be run with project's testing setup.
- Manual tests:
  - Create an admin account -> create election -> create candidates -> register voter -> cast vote -> confirm vote is logged and reflected in results.

Deployment (suggested)
----------------------
- Deploy on a LAMP stack or Docker.
- For Docker: create a Dockerfile and docker-compose.yml with a PHP-FPM + Nginx + MySQL service.
- Enable backups of the database and logs.
- Use environment variables for credentials, not committed files.

Contributing
------------
Contributions are welcome. Please:
1. Open an issue to discuss large changes
2. Create a branch for your changes
3. Submit a pull request with clear description and tests

License
-------
Choose a license (e.g., MIT). If you want, add a LICENSE file.

Contact
-------
Maintainer: nehemiahnab-max
Repository: https://github.com/nehemiahnab-max/online-voting

Notes & placeholders
--------------------
- Replace or expand sections according to your actual implementation (config filenames, scripts, schema path).
- If you want, I can generate example config files, SQL schema, or a Docker Compose file next.
