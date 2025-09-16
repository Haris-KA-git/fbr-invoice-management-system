# Expert Digital Invoice

Expert Digital Invoice - A comprehensive multi-tenant Real-Time Invoice Generation System for Pakistan's Federal Board of Revenue (FBR) Digital Invoicing API v1.12.

## Features

### 🏢 Multi-Tenant Architecture
- **Business Profile Management**: Multiple business profiles per user
- **Customer Management**: Registered and unregistered customer support
- **Item Catalog**: Complete product/service management with FBR compliance
- **Invoice Generation**: Real-time invoice creation with automatic calculations

### 🔐 Security & Authentication
- **Laravel Breeze Authentication**: Secure login/register system
- **Role-Based Access Control**: Admin, Accountant, Cashier, Auditor roles
- **Permission-Based UI**: Dynamic interface based on user permissions
- **Data Isolation**: Multi-tenant data security

### 📊 FBR Integration
- **API v1.12 Compliance**: Full integration with FBR Digital Invoicing API
- **Sandbox/Production Modes**: Switch between testing and live environments
- **Offline Queue System**: Handle API downtime with automatic retries
- **Real-time Validation**: Invoice validation before submission

### 📄 Invoice Features
- **PDF Generation**: Professional invoices with QR codes
- **Tax Calculations**: Automatic GST, FED, and other tax calculations
- **Multiple Invoice Types**: Sales, Purchase, Debit Note, Credit Note
- **FBR Compliance**: Full compliance with Pakistani tax regulations

### 📈 Reporting & Analytics
- **Dashboard**: Real-time statistics and charts
- **Export Options**: PDF, CSV, Excel export capabilities
- **Filter & Search**: Advanced filtering and search functionality
- **Audit Logs**: Complete activity tracking

## Technology Stack

- **Backend**: PHP Laravel 10+
- **Frontend**: Blade Templates + Bootstrap 5
- **Database**: MySQL 8+
- **Authentication**: Laravel Breeze with Spatie Laravel Permission
- **PDF Generation**: DOMPDF with QR Code support
- **API Integration**: Laravel HTTP Client

## Installation

1. **Clone the repository**
```bash
git clone <repository-url>
cd fbr-invoice-system
```

2. **Install dependencies**
```bash
composer install
```

3. **Environment setup**
```bash
cp .env.example .env
php artisan key:generate
```

4. **Database configuration**
```bash
# Update .env with your database credentials
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=fbr_invoice_system
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. **Run migrations and seeders**
```bash
php artisan migrate
php artisan db:seed
```

6. **Create storage link**
```bash
php artisan storage:link
```

7. **Start the development server**
```bash
php artisan serve
```

## Demo Accounts

The system comes with pre-configured demo accounts:

| Role | Email | Password | Access Level |
|------|-------|----------|--------------|
| Admin | admin@fbrvoice.com | admin123 | Full system access |
| Accountant | accountant@fbrvoice.com | accountant123 | Invoice & report management |
| Cashier | cashier@fbrvoice.com | cashier123 | Invoice creation only |
| Auditor | auditor@fbrvoice.com | auditor123 | Read-only access |
| Demo Business | demo@business.com | demo123 | Sample business owner |

## FBR API Configuration

1. **Get FBR API Token**
   - Register with PRAL (Pakistan Revenue Automation Limited)
   - Obtain Bearer Token (valid for 5 years)
   - Whitelist your server IP addresses

2. **Configure Business Profile**
   - Add FBR API Token to business profile
   - Set sandbox/production mode
   - Configure whitelisted IPs

3. **Environment Variables**
```bash
FBR_SANDBOX_URL=https://esp.fbr.gov.pk:8244/FBR/v1
FBR_PRODUCTION_URL=https://esp.fbr.gov.pk/FBR/v1
FBR_API_TIMEOUT=30
FBR_MAX_RETRIES=3
```

## Usage

### Creating Invoices
1. Set up business profile with FBR credentials
2. Add customers (registered/unregistered)
3. Create item catalog with proper tax rates
4. Generate invoices with automatic tax calculations
5. Submit to FBR for validation and approval

### Managing Business Operations
- **Dashboard**: Monitor invoice statistics and FBR submission status
- **Reports**: Generate comprehensive business reports
- **Audit Logs**: Track all system activities
- **User Management**: Control access with role-based permissions

## Testing

Run the test suite:
```bash
php artisan test
```

## Queue Processing

For production environments, set up queue processing:
```bash
php artisan queue:work
```

Schedule the FBR queue processor:
```bash
php artisan schedule:work
```

## Security Considerations

- **Data Encryption**: TLS 1.2+ in transit, AES-256 at rest
- **IP Whitelisting**: FBR API requires IP whitelisting
- **Token Security**: Secure storage of FBR Bearer tokens
- **Audit Logging**: Complete activity tracking
- **Role-Based Access**: Granular permission system

## Support

For support and documentation:
- Check the built-in help system
- Review the demo data for examples
- Consult FBR Digital Invoicing API documentation

## License

This project is licensed under the MIT License.