# Nilai Care Medical Centre Appointment System

A PHP and MySQL hospital appointment website with separate patient and
administrator areas. Patients can register, maintain their profile, view doctor
schedules, book an available date and time, and edit an active appointment.
Administrators can manage patients, doctors, schedules, and appointments.

## Main Features

### Patient area

- Register and log in securely.
- View active doctors and their availability.
- Book only on the selected doctor's working days and within the saved time range.
- Choose from clear 30-minute time options.
- Edit the doctor, date, time, and reason for pending or approved appointments.
- Cancel pending or approved appointments.
- Edit name, email, phone, and password from **My Profile**.
- Return to the hospital homepage from the main pages.
- Use a clear, professional patient portal for login and registration.

When a patient edits an approved appointment, it returns to **pending** so an
administrator can review the changed booking.

### Administrator area

- Dashboard with patient, doctor, appointment, and pending totals.
- Add, edit, and remove patients.
- Add doctors and edit their details, availability, and active/inactive status.
- Book an available appointment for a patient.
- Edit an appointment's patient, doctor, date, time, reason, and status.
- Approve, cancel, or complete appointments.
- Edit the administrator username and password.
- Return to the public hospital homepage from the sidebar.
- Use a separate, clearly labelled administrator login portal.
- Use a professional responsive admin dashboard with a sidebar, statistic
  cards, quick actions, and a structured administrator profile page.

Doctors with appointment history cannot be deleted; set them to **inactive** to
preserve appointment records.

## Availability Rules

Doctor schedules are maintained in **Admin → Doctors**.

- Days may be saved as `Mon, Wed, Fri` or as a range such as `Mon - Fri`.
- Time must be a range such as `9:00 AM - 12:00 PM`.
- The booking page creates 30-minute choices inside that range.
- Both the browser and PHP server validate the schedule.
- A doctor cannot have two non-cancelled appointments at the same date and time.

The PHP validation is authoritative, so changing the browser form cannot bypass
the availability rules.

## Run with XAMPP

1. Install XAMPP from <https://www.apachefriends.org>.
2. Start **Apache** and **MySQL** in the XAMPP Control Panel.
3. Copy the complete `hospital-appointment-system` folder into `htdocs`.
4. Open `http://localhost/phpmyadmin`.
5. Select **Import**, choose `database.sql`, and complete the import.
6. Open `http://localhost/hospital-appointment-system/`.

`database.sql` is intended for a fresh installation. It creates the database,
four tables, sample doctors, an appointment-slot index, and the default admin.

## Default Login

Patient: create an account using the **Register** page.

Administrator:

- URL: `http://localhost/hospital-appointment-system/admin/login.php`
- Username: `admin`
- Password: `admin123`

Change the default administrator password through **Admin Profile** after the
first login.

## Project Structure

```text
hospital-appointment-system/
├── assets/
│   ├── booking.js                  # Schedule-aware date/time form behaviour
│   └── style.css                   # Responsive hospital and admin design
├── includes/
│   ├── appointment_helpers.php     # Server-side schedule and clash validation
│   ├── footer.php
│   └── header.php
├── admin/
│   ├── includes/                   # Admin sidebar layout
│   ├── add_patient.php
│   ├── appointments.php
│   ├── book_appointment.php
│   ├── dashboard.php
│   ├── doctors.php
│   ├── edit_appointment.php
│   ├── edit_doctor.php
│   ├── edit_patient.php
│   ├── login.php
│   ├── logout.php
│   ├── patients.php
│   └── profile.php
├── book_appointment.php
├── config.php                      # Database and hospital settings
├── dashboard.php
├── database.sql
├── doctors.php
├── edit_appointment.php
├── index.php
├── login.php
├── logout.php
├── profile.php
└── register.php
```

## Technical Notes

- Passwords use `password_hash()` and `password_verify()`.
- Database writes that contain user input use prepared statements.
- Session identifiers are regenerated after successful login.
- Dynamic content is escaped with `htmlspecialchars()` before display.
- The responsive design supports desktop and smaller mobile screens.
- The stylesheet link includes a version value so browsers load the corrected
  design instead of showing an older cached login layout.
- The database contains four related tables: `users`, `doctors`,
  `appointments`, and `admins`.
- Appointment status flow is normally `pending → approved → completed`, with
  `cancelled` available for bookings that should not proceed.

This project uses the fictional name **Nilai Care Medical Centre** for a
realistic academic demonstration.
