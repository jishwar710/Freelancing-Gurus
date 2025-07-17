# FreelancingGurus

FreelancingGurus is a web-based platform designed to connect skilled freelancers with educational institutes seeking instructors for various courses. The platform facilitates job posting, application management, and communication between freelancers and institutes, all managed through a comprehensive admin panel.

## Key Features

### Admin Panel
- **Dashboard:** Provides a statistical overview of total institutes, freelancers, job applications, and jobs posted.
- **User Management:** Allows administrators to manage institute and freelancer accounts.
- **Job Listing Management:** Admins can view and manage all job listings on the platform.
- **Feedback and Reports:** View user feedback and reports submitted by freelancers.

### Freelancer Module
- **Registration and Profile:** Freelancers can create and manage their profiles, showcasing their qualifications and experience.
- **Job Search and Application:** Search for relevant job opportunities and apply with a single click.
- **Application Tracking:** Keep track of application statuses and view responses from institutes.
- **Reporting:** Report any suspicious or inappropriate job postings.

### Institute Module
- **Registration and Profile:** Institutes can register and create profiles to attract qualified freelancers.
- **Job Posting:** Post detailed job listings, including required skills, experience, and salary.
- **Application Review:** Review applications from freelancers and manage communication.
- **Response System:** Send responses to applicants directly through the platform.

## Technologies Used

- **Backend:** PHP
- **Database:** MySQL / MariaDB
- **Frontend:** HTML, Tailwind CSS
- **Libraries:**
  - [PHPMailer](https://github.com/PHPMailer/PHPMailer): For sending email notifications.

## Database Schema

The database is structured to manage users, jobs, and their interactions efficiently. Key tables include:

- `free_user`: Stores information about freelancer users.
- `institute_details`: Stores information about institute users.
- `job_details`: Contains all job postings.
- `applications`: Manages job applications from freelancers.
- `institute_responses`: Stores responses from institutes to freelancers.
- `reports`: Contains reports submitted by freelancers regarding job postings.
- `feedback`: Stores user feedback about the platform.

## Installation and Setup

To set up the project locally, follow these steps:

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/amol1027/Freelancing-Gurus.git
    cd Freelancing-Gurus
    ```

2.  **Import the database:**
    - Create a new database named `freelance` in your MySQL/MariaDB server.
    - Import the `DataBase/freelance.sql` file into the newly created database.

3.  **Configure the database connection:**
    - The database connection is configured in multiple files. You may need to update the database credentials (`hostname`, `username`, `password`, `database name`) in the following files:
        - `admin/admin_dboard.php`
        - `admin/AdminLogin.php`
        - And other relevant PHP files that connect to the database.

4.  **Install dependencies:**
    - If you have Composer installed, run the following command to install the required PHP packages:
    ```bash
    composer install
    ```

## How to Use

1.  **Admin:**
    - Navigate to `admin/AdminLogin.php` to log in.
    - Use the dashboard to monitor platform activity and manage users and jobs.

2.  **Freelancer:**
    - Register for a new account or log in.
    - Complete your profile.
    - Browse and apply for jobs.
    - Check your application status in the "My Applications" section.

3.  **Institute:**
    - Register your institute and wait for admin verification.
    - Once verified, log in and post job openings.
    - Review applications and respond to candidates.
