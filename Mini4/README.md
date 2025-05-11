# Presence Plus - Attendance Management System

## Overview
Presence Plus is a modern web-based attendance management system that uses facial recognition technology to track student attendance. The system provides secure authentication, separate dashboards for students and teachers, and real-time attendance tracking capabilities.

## Features
- Face recognition-based attendance marking
- Secure user authentication system
- Separate dashboards for students and teachers
- Real-time attendance tracking and monitoring
- Attendance history and reporting
- Request management system for students
- Class management for teachers
- Responsive design for all screen sizes
- Modern and intuitive user interface

## System Requirements
- XAMPP (with PHP and MySQL)
- Modern web browser (Chrome, Firefox, Safari, or Edge)
- Webcam for face recognition features

## Project Structure
- `index.php` - Landing page with feature showcase
- `signin.php` - User authentication page
- `signup.php` - New user registration
- `student-dashboard.php` - Student interface with attendance history
- `teacher-dashboard.php` - Teacher interface for class management
- `attendance.php` - Face recognition-based attendance marking
- `start-class.php` - Class creation and management for teachers
- `connect.php` - Database connection configuration
- `logout.php` - Session management
- `submit-request.php` - Student attendance request handling

## Setup Instructions

1. Install XAMPP
   - Download and install XAMPP from the official website
   - Ensure Apache and MySQL services are running

2. Project Setup
   - Clone or download the project files
   - Place the files in the `htdocs` directory of your XAMPP installation
   - Import the database schema (if provided)

3. Configuration
   - Update database credentials in `connect.php`
   - Ensure webcam permissions are enabled in your browser

4. Access the Application
   - Open your web browser
   - Visit `http://localhost/Mini4`

## Usage Guide

### For Students
1. Register or sign in to your account
2. Access the student dashboard
3. Use the face recognition feature to mark attendance
4. View attendance history
5. Submit attendance requests if needed

### For Teachers
1. Sign in with teacher credentials
2. Create and manage classes
3. Monitor student attendance
4. Generate attendance reports
5. Handle student requests

## Technical Details
- Built with PHP for backend processing
- Uses face-api.js for facial recognition
- Implements session-based authentication
- Responsive design using modern CSS
- Real-time face detection and verification

## Security Features
- Secure password hashing
- Session management
- Protected routes for authenticated users
- Role-based access control

## Browser Compatibility
- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

## License
© 2024 Presence Plus. All rights reserved.