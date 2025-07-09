# FINONEST TeleCRM - PHP Version

A professional telecalling CRM system built with PHP, designed specifically for financial services, loan agents, and sales teams.

## Features

- **Dashboard**: Comprehensive overview with real-time statistics
- **Lead Management**: Track and manage prospects effectively
- **Auto Dialer**: Smart calling system with native mobile support
- **Campaign Management**: Create and manage calling campaigns
- **Message Center**: WhatsApp, SMS, and Email templates
- **Schedule Manager**: Manage calls, meetings, and follow-ups
- **Analytics**: Performance insights and reporting
- **Settings**: Comprehensive configuration options

## Technology Stack

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **Styling**: Custom CSS with CSS Variables
- **Icons**: Custom icon font
- **Mobile**: Responsive design with mobile-first approach

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd telecrm-php
   ```

2. **Database Setup**
   - Create a MySQL database named `telecrm`
   - Import the schema: `mysql -u username -p telecrm < database/schema.sql`
   - Update database credentials in `config/database.php`

3. **Web Server Configuration**
   - Point your web server document root to the project directory
   - Ensure PHP has write permissions for session handling
   - Enable PHP extensions: PDO, PDO_MySQL

4. **Default Login**
   - Email: `admin@finonest.com`
   - Password: `password`

## File Structure

```
/
├── config/
│   └── database.php          # Database configuration
├── includes/
│   ├── auth.php             # Authentication functions
│   ├── header.php           # Common header
│   └── footer.php           # Common footer
├── components/
│   ├── sidebar.php          # Navigation sidebar
│   ├── header-bar.php       # Top header bar
│   ├── quick-actions.php    # Dashboard quick actions
│   └── recent-activity.php  # Recent activity component
├── assets/
│   ├── css/
│   │   ├── style.css        # Main stylesheet
│   │   ├── auth.css         # Authentication styles
│   │   └── icons.css        # Icon definitions
│   ├── js/
│   │   ├── main.js          # Main JavaScript
│   │   └── dialer.js        # Dialer functionality
│   └── images/              # Image assets
├── api/                     # API endpoints
├── database/
│   └── schema.sql           # Database schema
├── index.php                # Dashboard
├── login.php                # Login page
├── leads.php                # Lead management
├── dialer.php               # Auto dialer
├── campaigns.php            # Campaign management
├── messages.php             # Message center
├── schedule.php             # Schedule manager
├── analytics.php            # Analytics dashboard
└── settings.php             # Settings page
```

## Key Features

### Dashboard
- Real-time statistics and KPIs
- Quick action buttons
- Recent activity feed
- Priority tasks overview

### Lead Management
- Comprehensive lead database
- Status tracking (Hot, Warm, Cold)
- Search and filtering
- Bulk operations

### Auto Dialer
- Native mobile calling support
- Call queue management
- Call disposition tracking
- Auto-dialing capabilities
- Break management

### Campaign Management
- Campaign creation and management
- Lead assignment
- Progress tracking
- Performance analytics

### Message Center
- Template management
- WhatsApp, SMS, Email support
- Quick send functionality
- Usage tracking

### Analytics
- Call performance metrics
- Agent performance tracking
- Conversion analytics
- Trend analysis

## Security Features

- Session-based authentication
- Password hashing with PHP's password_hash()
- SQL injection prevention with prepared statements
- XSS protection with htmlspecialchars()
- CSRF protection ready

## Mobile Support

- Responsive design for all screen sizes
- Mobile-optimized dialer interface
- Touch-friendly controls
- Native calling integration

## Browser Support

- Chrome 70+
- Firefox 65+
- Safari 12+
- Edge 79+

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## License

This project is licensed under the MIT License.

## Support

For support and questions, please contact the development team.

---

**FINONEST TeleCRM** - Trust comes first.