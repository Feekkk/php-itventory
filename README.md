# RCMP-ITventory - IT Equipment Inventory Management System

A comprehensive web-based IT equipment inventory management system designed for the Razak Faculty of Technology and Informatics (RCMP), University Kuala Lumpur. This system enables technicians to manage equipment inventory, track handovers, and monitor equipment status efficiently.

## 📋 Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Technologies Used](#technologies-used)
- [Database Structure](#database-structure)
- [Installation & Setup](#installation--setup)
- [Project Structure](#project-structure)
- [Key Features](#key-features)
- [User Roles](#user-roles)
- [Usage Guide](#usage-guide)
- [Contributing](#contributing)
- [License](#license)

## 🎯 Overview

RCMP-ITventory is a PHP-based inventory management system that streamlines the process of managing IT equipment for educational institutions. The system allows technicians to:

- Manage equipment inventory with detailed information
- Track equipment handovers to lecturers
- Monitor equipment status (Available, In Use, Maintenance, Reserved, Hand Over)
- Categorize equipment for easy organization
- Generate pickup requests and manage handover processes
- View detailed equipment information and history

## ✨ Features

### Inventory Management
- **Add Equipment**: Add new equipment items with detailed information (ID, name, category, brand, model, serial number, status, location, description)
- **List Inventory**: Browse all equipment with search and filter capabilities (by category, status, name)
- **View Equipment Details**: View comprehensive information about each equipment item
- **Update Equipment Status**: Change equipment status (Available, In Use, Maintenance, Reserved)
- **Category Management**: Organize equipment into categories (Laptops, Projectors, Monitors, Printers, Tablets, Accessories, Cables & Adapters, Networking, Audio/Visual)

### Handover Management
- **Create Pickup Requests**: Create pickup requests for equipment handover to lecturers
- **View Pending Handovers**: View all pending handover requests in a table format
- **View Handover Records**: View all completed handovers (picked up and returned) in a table format
- **Handover Process**: Complete handover process with user agreement and confirmation
- **Track Handover Status**: Monitor handover status (Pending, Picked Up, Returned)
- **Staff Tracking**: Track which staff member performed the handover

### User Management
- **User Authentication**: Secure login and registration system
- **User Profile**: Manage user profile information (name, email, password)
- **Session Management**: Secure session handling for user authentication

### Dashboard
- **Quick Actions**: Access to main features (Equipment Inventory, Pickup Equipment, Equipment Disposal, History)
- **User Information**: Display user information card with staff details
- **Statistics**: View handover statistics (Total, Pending, Picked Up, Returned)

## 🛠 Technologies Used

- **Backend**: PHP 7.4+
- **Database**: MySQL (MariaDB)
- **Frontend**: HTML5, CSS3, JavaScript
- **Styling**: Custom CSS with modern design principles
- **Authentication**: Session-based authentication
- **Database Access**: MySQLi (MySQL Improved Extension)

## 🗄 Database Structure

The system uses the following database tables:

### `technician`
Stores technician/staff information:
- `id`: Primary key
- `staff_id`: Unique staff identification number
- `full_name`: Full name of the technician
- `email`: Email address (unique)
- `password`: Hashed password
- `created_at`, `updated_at`: Timestamps

### `categories`
Stores equipment categories:
- `id`: Primary key
- `category_name`: Unique category name
- `description`: Category description
- `created_at`, `updated_at`: Timestamps

### `inventory`
Stores equipment inventory:
- `id`: Primary key
- `equipment_id`: Unique equipment identifier
- `equipment_name`: Equipment name
- `category_id`: Foreign key to categories table
- `brand`: Equipment brand
- `model`: Equipment model
- `serial_number`: Serial number
- `status`: Equipment status (Available, In Use, Maintenance, Reserved, Hand Over)
- `location`: Equipment location
- `description`: Equipment description
- `created_at`, `updated_at`: Timestamps

### `handover`
Stores handover records:
- `handoverID`: Primary key
- `equipment_id`: Foreign key to inventory table
- `equipment_name`: Equipment name
- `lecturer_id`: Lecturer identification number
- `lecturer_name`: Lecturer full name
- `lecturer_email`: Lecturer email address
- `lecturer_phone`: Lecturer phone number
- `pickup_date`: Pickup date
- `return_date`: Return date
- `handoverStat`: Handover status (pending, picked_up, returned)
- `handoverStaff`: Staff ID who performed the handover
- `returnStaff`: Staff ID who received the return
- `created_at`, `updated_at`: Timestamps

## 📦 Installation & Setup

### Prerequisites
- PHP 7.4 or higher
- MySQL 5.7+ or MariaDB 10.3+
- Web server (Apache/Nginx) or PHP built-in server
- Composer (optional, for dependency management)

### Installation Steps

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd RCMP-ITventory
   ```

2. **Configure Database**
   - Update `config/database.php` with your database credentials:
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'your_username');
     define('DB_PASS', 'your_password');
     define('DB_NAME', 'rcmp_itventory');
     ```

3. **Create Database**
   - Option 1: Run the SQL script manually:
     ```bash
     mysql -u root -p < database/schema.sql
     ```
   - Option 2: Use the setup script:
     - Update `database/setup.php` with your database credentials
     - Run `php database/setup.php` in your browser

4. **Set Up Web Server**
   - For Apache: Configure virtual host pointing to the project directory
   - For PHP built-in server:
     ```bash
     php -S localhost:8000
     ```

5. **Access the Application**
   - Open your browser and navigate to `http://localhost:8000` (or your configured domain)
   - Register a new technician account
   - Login with your credentials

## 📁 Project Structure

```
RCMP-ITventory/
├── admin/                 # Admin panel (if applicable)
│   └── dashboard.php
├── auth/                  # Authentication modules
│   ├── login.php
│   ├── logout.php
│   ├── register.php
│   └── session.php
├── component/             # Reusable components
│   ├── footer.php
│   └── header.php
├── css/                   # Stylesheets
│   ├── AddInventoryItem.css
│   ├── AddPickup.css
│   ├── dashboard.css
│   ├── footer.css
│   ├── home.css
│   ├── inventory.css
│   ├── login.css
│   ├── Pickup.css
│   ├── PickupForm.css
│   ├── profile.css
│   └── ViewItem.css
├── config/                # Application configuration files
│   ├── database.php
│   ├── email.php
│   └── handover_email.php
├── database/              # Database schema and setup scripts
│   ├── schema.sql
│   └── setup.php
├── js/                    # JavaScript files
│   ├── AddPickup.js
│   ├── dashboard.js
│   ├── home.js
│   ├── inventory.js
│   ├── login.js
│   ├── Pickup.js
│   ├── PickupForm.js
│   ├── profile.js
│   ├── register.js
│   └── ViewItem.js
├── public/                # Public assets
│   ├── background.jpg
│   ├── bgm.png
│   ├── rcmp-white.png
│   ├── unikl-rcmp.png
│   └── unikl-word.png
├── technician/            # Technician panel
│   ├── AddCategoriesItem.php
│   ├── AddInventoryItem.php
│   ├── AddPickup.php
│   ├── dashboard.php
│   ├── Disposal.php
│   ├── History.php
│   ├── ListInventory.php
│   ├── Pickup.php
│   ├── PickupForm.php
│   ├── profile.php
│   ├── ViewHandover.php
│   ├── ViewItem.php
│   └── ViewPending.php
├── index.php              # Home page
└── README.md              # This file
```

## 🎯 Key Features

### 1. Equipment Inventory Management

#### Add Equipment (`AddInventoryItem.php`)
- Add new equipment with comprehensive details
- Select category from dropdown
- Set equipment status
- Add brand, model, serial number, location, and description
- Category filtering for easy selection

#### List Inventory (`ListInventory.php`)
- View all equipment in a table format
- Search by equipment ID, name, or description
- Filter by category and status
- View equipment details by clicking on equipment
- Statistics display (total items, category counts)

#### View Equipment (`ViewItem.php`)
- View detailed equipment information
- Update equipment status (except when status is "Hand Over")
- View equipment category, brand, model, serial number, location, status, and description
- Status change restrictions for handover items

### 2. Handover Management

#### Create Pickup Request (`AddPickup.php`)
- Create pickup requests for equipment handover
- Select equipment from available inventory
- Filter equipment by category
- Enter lecturer information (ID, name, email, phone)
- Set pickup date and return date
- Automatically updates equipment status to "Hand Over"

#### Pickup Management (`Pickup.php`)
- View all handovers in two sections: Pending and Hand Over
- Statistics dashboard (Total, Pending, Picked Up, Returned)
- Search and filter functionality
- Quick access to view all pending or handover records
- Simplified card view for quick overview

#### View Pending Handovers (`ViewPending.php`)
- Table view of all pending handovers
- Search functionality
- View detailed information about each pending handover
- Access to handover form for confirmation

#### View Handover Records (`ViewHandover.php`)
- Table view of all completed handovers (picked up and returned)
- Search and status filter functionality
- Display handover staff information
- View detailed handover information

#### Handover Form (`PickupForm.php`)
- View detailed equipment and handover information
- User agreement section for pending handovers
- Confirm handover process
- Track handover staff
- View-only mode for completed handovers

### 3. Category Management

#### Add Categories (`AddCategoriesItem.php`)
- Add new equipment categories
- Category name and description
- Automatic category creation if table doesn't exist

### 4. User Profile

#### Profile Management (`profile.php`)
- View and update user information
- Change password
- Update email and full name
- Secure password hashing

### 5. Dashboard

#### Dashboard (`dashboard.php`)
- Welcome message with user name
- Quick action cards:
  - Equipment Inventory
  - Pickup Equipment
  - Equipment Disposal (placeholder)
  - History (placeholder)
- User information card displaying:
  - Full Name
  - Staff ID
  - Email

## 👥 User Roles

### Technician
- Manage equipment inventory
- Create and manage pickup requests
- Process handovers
- View handover records
- Update equipment status
- Manage user profile

## 📖 Usage Guide

### Adding Equipment

1. Navigate to **Equipment Inventory** from the dashboard
2. Click **Add Equipment** button
3. Fill in the equipment details:
   - Equipment ID (unique identifier)
   - Equipment Name
   - Category (select from dropdown)
   - Brand, Model, Serial Number
   - Status (Available, In Use, Maintenance, Reserved)
   - Location
   - Description
4. Click **Add Equipment** to save

### Creating Pickup Request

1. Navigate to **Pickup Equipment** from the dashboard
2. Click **Add Pickup** button
3. Select equipment from the dropdown (filtered by category)
4. Enter lecturer information:
   - Lecturer ID
   - Lecturer Name
   - Lecturer Email
   - Lecturer Phone (optional)
5. Set pickup date and return date
6. Click **Submit** to create the request

### Processing Handover

1. Navigate to **Pickup Equipment** from the dashboard
2. View pending handovers in the **Pending** section
3. Click **View All** to see all pending handovers
4. Click the **View** icon on a pending handover
5. Review equipment and handover details
6. Read and agree to the user agreement
7. Click **Confirm Handover** to complete the process

### Viewing Handover Records

1. Navigate to **Pickup Equipment** from the dashboard
2. View completed handovers in the **Hand Over** section
3. Click **View All** to see all handover records
4. Use search and filter to find specific records
5. Click the **View** icon to see detailed information

### Updating Equipment Status

1. Navigate to **Equipment Inventory** from the dashboard
2. Search or filter to find the equipment
3. Click on the equipment to view details
4. Select new status from the dropdown
5. Status will be updated automatically (except for "Hand Over" status)

## 🔒 Security Features

- Session-based authentication
- Password hashing (bcrypt)
- SQL injection prevention (prepared statements)
- XSS protection (HTML entity encoding)
- CSRF protection (session tokens)
- Input validation and sanitization
- Access control (login required for all pages)

## 🚀 Future Enhancements

- Equipment disposal management
- Transaction history tracking
- Reporting and analytics
- Email notifications
- Equipment maintenance scheduling
- Barcode/QR code support
- Mobile responsive design improvements
- Admin panel for user management
- Equipment reservation system
- Multi-language support

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 License

This project is licensed under the MIT License - see the LICENSE file for details.

## 👨‍💻 Author

Developed for IT - department (UniKL Royal College of Medicine).

## 📞 Support

For support, please contact the development team or create an issue in the repository.

## 🙏 Acknowledgments

- UniKL Royal College of Medicine (RCMP)
- All contributors and testers

---

**Note**: This system is designed for educational purposes and internal use within the IT department (UniKL RCMP). Ensure proper security measures are in place before deploying to production.
