# Attendance Checker System

A comprehensive web-based attendance management system using QR code scanning technology. Built with HTML, CSS, JavaScript, PHP, and MySQL.

## 🌟 Features

- **Student Registration**: Add new students with automatic QR code generation
- **QR Code Scanning**: Real-time camera-based QR code scanning for attendance
- **Manual Entry**: Backup option for manual attendance marking
- **Student Management**: View, search, and manage all registered students
- **Attendance Reports**: Generate detailed reports with charts and statistics
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Export/Print**: Export data to CSV and print reports/QR codes

## 📋 Requirements

- Web server with PHP support (Apache/Nginx)
- MySQL database
- Modern web browser with camera support
- PHP 7.4 or higher
- MySQL 5.7 or higher

## 🚀 Installation

### 1. Database Setup

1. Create a MySQL database named `attendance_system`
2. Import the database structure:
   ```sql
   mysql -u your_username -p attendance_system < database/setup.sql
   ```

### 2. Configuration

1. Edit `includes/database.php` and update your database credentials:
   ```php
   private $host = 'localhost';
   private $db_name = 'attendance_system';
   private $username = 'your_mysql_username';
   private $password = 'your_mysql_password';
   ```

### 3. Web Server Setup

1. Copy all files to your web server directory (e.g., `htdocs`, `www`, or `public_html`)
2. Ensure the web server has read/write permissions
3. Access the application through your web browser

## 📁 File Structure

```
attendance-checker/
├── api/                          # PHP API endpoints
│   ├── get_attendance_report.php # Generate attendance reports
│   ├── get_dashboard_stats.php   # Dashboard statistics
│   ├── get_students.php          # Fetch all students
│   ├── get_student_details.php   # Individual student details
│   ├── get_today_attendance.php  # Today's attendance
│   ├── mark_attendance.php       # Mark student attendance
│   └── register_student.php      # Register new student
├── css/
│   └── style.css                 # Main stylesheet
├── database/
│   └── setup.sql                 # Database structure
├── includes/
│   └── database.php              # Database connection
├── js/
│   └── main.js                   # JavaScript utilities
├── attendance_report.php         # Reports page
├── index.php                     # Homepage/Dashboard
├── register_student.php          # Student registration
├── scan_attendance.php           # QR code scanner
├── view_students.php             # Student management
└── README.md                     # This file
```

## 🎯 Usage

### For Teachers/Administrators:

1. **Register Students**:
   - Go to "Register Student"
   - Fill in student details
   - System generates unique QR code
   - Print QR code for student

2. **Take Attendance**:
   - Go to "Scan Attendance"
   - Allow camera access
   - Point camera at student QR codes
   - Attendance is automatically recorded

3. **View Reports**:
   - Go to "Attendance Report"
   - Select date range and class
   - View detailed statistics and charts
   - Export to CSV or print reports

### For Students:

1. Get your QR code from the teacher
2. Present QR code when attendance is being taken
3. Keep QR code safe and readable

## 🔧 Configuration Options

### Time Settings
- Modify late threshold in `api/mark_attendance.php` (default: 9:00 AM)
- Adjust timezone in PHP configuration if needed

### QR Code Settings
- QR codes are generated using Google QR Code API
- Format: `STUDENT_ID|TIMESTAMP`
- Can be customized in `api/register_student.php`

### Classes
- Modify available classes in registration and filter forms
- Default classes: 10-A through 12-C

## 🛡️ Security Considerations

1. **Database Security**:
   - Use strong database passwords
   - Limit database user permissions
   - Consider using environment variables for credentials

2. **Input Validation**:
   - All inputs are validated and sanitized
   - Prepared statements prevent SQL injection

3. **Camera Access**:
   - Camera access requires user permission
   - No images are stored or transmitted

## 🌐 Browser Compatibility

- ✅ Chrome (recommended)
- ✅ Firefox
- ✅ Safari
- ✅ Edge
- ⚠️ Internet Explorer (limited support)

## 📱 Mobile Support

The application is fully responsive and works on:
- Smartphones (iOS/Android)
- Tablets
- Desktop computers

## 🎨 Customization

### Styling
- Modify `css/style.css` for custom colors and layout
- Uses CSS Grid and Flexbox for responsive design
- Gradient backgrounds and modern UI elements

### Functionality
- Add new fields to student registration
- Modify attendance rules and late policies
- Extend reporting features

## 🐛 Troubleshooting

### Common Issues:

1. **Camera not working**:
   - Ensure HTTPS connection (required for camera access)
   - Check browser permissions
   - Try different browser

2. **Database connection errors**:
   - Verify database credentials
   - Check database server status
   - Ensure database exists

3. **QR codes not scanning**:
   - Ensure good lighting
   - Clean camera lens
   - Try manual entry as backup

## 📖 Learning Objectives

This project helps beginners learn:

- **Frontend Development**: HTML5, CSS3, JavaScript (ES6+)
- **Backend Development**: PHP, MySQL
- **Web APIs**: Camera access, QR code generation
- **Database Design**: Relational database structure
- **Responsive Design**: Mobile-first approach
- **AJAX**: Asynchronous communication
- **Data Visualization**: Charts and reports

## 🤝 Contributing

Feel free to:
- Report bugs
- Suggest new features
- Submit pull requests
- Improve documentation

## 📄 License

This project is open-source and available for educational purposes.

## 🙏 Acknowledgments

- QR code generation: Google QR Code API
- Charts: Chart.js library
- QR code scanning: ZXing library
- Icons: Unicode emojis

---

**Happy Learning! 🎓**

For questions or support, please check the code comments or create an issue.
