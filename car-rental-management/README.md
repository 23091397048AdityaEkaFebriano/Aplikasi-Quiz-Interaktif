# Car Rental Management System

## Overview
This project is a web-based information management system for car rental services. It allows users to manage car rentals efficiently, with distinct functionalities for two user roles: Admin and Borrower.

## Features
- **User Roles**: 
  - **Admin**: Can manage users, cars, and view reports.
  - **Borrower**: Can view available cars, rent cars, and manage their rentals.
  
- **User Management**: Admin can add, edit, and delete user accounts (both Admin and Borrower).

- **Car Management**: Admin can add, edit, and delete car listings.

- **Rental Management**: Borrowers can view available cars and initiate rental transactions.

- **Payment Processing**: The system handles payment transactions related to rentals.

## Project Structure
```
car-rental-management
├── public
│   ├── css
│   │   └── styles.css
│   ├── js
│   │   └── main.js
│   ├── index.html
│   └── bootstrap
│       └── bootstrap.min.css
├── src
│   ├── config
│   │   └── db.php
│   ├── controllers
│   │   ├── admin.php
│   │   ├── borrower.php
│   │   └── auth.php
│   ├── models
│   │   ├── car.php
│   │   ├── user.php
│   │   ├── rental.php
│   │   ├── payment.php
│   │   └── vehicle_type.php
│   ├── views
│   │   ├── admin_dashboard.php
│   │   ├── borrower_dashboard.php
│   │   ├── login.php
│   │   ├── register.php
│   │   └── car_list.php
│   └── utils
│       └── helpers.php
├── sql
│   └── schema.sql
├── .gitignore
└── README.md
```

## Database Structure
The database consists of the following tables:

1. **users**: Stores user information (id, username, password, role).
2. **cars**: Stores car information (id, make, model, year, type_id, availability).
3. **rentals**: Stores rental transactions (id, user_id, car_id, rental_date, return_date).
4. **payments**: Stores payment information (id, rental_id, amount, payment_date).
5. **vehicle_types**: Stores types of vehicles (id, type_name).

## Setup Instructions
1. Clone the repository to your local machine.
2. Import the SQL schema located in the `sql/schema.sql` file into your database.
3. Configure the database connection in `src/config/db.php`.
4. Open `public/index.html` in your web browser to access the application.

## Testing
- Create test accounts for both Admin and Borrower roles using the registration form.
- Log in with the created accounts to test the functionalities.

## Technologies Used
- HTML, CSS, JavaScript for front-end development.
- PHP for server-side scripting.
- MySQL for database management.
- Bootstrap for responsive design.

## License
This project is open-source and available for modification and distribution.