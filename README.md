# E-Commerce Order Management System

A scalable REST API for order management with inventory tracking, built with Laravel 10+ and modern architectural patterns.

## 🚀 Features

### Product & Inventory Management
- ✅ Product CRUD operations with variants support
- ✅ Real-time inventory tracking with reservation system
- ✅ Low stock alerts via queue jobs
- ✅ Bulk product import via CSV
- ✅ Full-text product search using Laravel Scout

### Order Processing
- ✅ Multi-item order creation with inventory validation
- ✅ Order status workflow: Pending → Processing → Shipped → Delivered → Cancelled
- ✅ Automatic inventory deduction on order confirmation
- ✅ Order rollback on cancellation (automatic inventory restoration)
- ✅ PDF invoice generation
- ✅ Automated email notifications for order updates

### Authentication & Authorization
- ✅ JWT authentication with refresh tokens
- ✅ Role-based access control (Admin, Vendor, Customer)
- ✅ Granular permissions per role

### Performance & Scalability
- ✅ Query optimization with eager loading
- ✅ Database indexing on searchable fields
- ✅ Response pagination
- ✅ Redis caching and queue processing
- ✅ Database sharding strategy documentation

## 📋 Requirements

- PHP 8.2 or higher
- Composer 2.x
- MySQL 8.0 or PostgreSQL 14+
- Redis 6.0+
- Node.js 18+ (for frontend assets if needed)

## 🛠️ Tech Stack

- **Framework:** Laravel 10.x
- **Authentication:** tymon/jwt-auth
- **Search:** Laravel Scout + Meilisearch
- **PDF Generation:** barryvdh/laravel-dompdf
- **Queue:** Redis
- **Cache:** Redis
- **Testing:** PHPUnit

## 📦 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/ecommerce-order-management.git
cd ecommerce-order-management
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Environment Setup

```bash
cp .env.example .env
php artisan key:generate
php artisan jwt:secret
```

### 4. Configure Environment Variables

Edit `.env` file with your configuration:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce_order_management
DB_USERNAME=root
DB_PASSWORD=

REDIS_HOST=127.0.0.1
REDIS_PORT=6379

QUEUE_CONNECTION=redis
CACHE_DRIVER=redis

SCOUT_DRIVER=meilisearch
MEILISEARCH_HOST=http://127.0.0.1:7700
MEILISEARCH_KEY=

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
```

### 5. Database Setup

```bash
php artisan migrate
php artisan db:seed
```

### 6. Start Services

```bash
# Start Laravel development server
php artisan serve

# Start queue worker (in a separate terminal)
php artisan queue:work

# Start Meilisearch (if using for search)
./meilisearch --master-key=YOUR_MASTER_KEY
```

## 🔐 Authentication

### Register a New User

```bash
POST /api/v1/auth/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "role": "customer"
}
```

### Login

```bash
POST /api/v1/auth/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response:**
```json
{
  "message": "Login successful",
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "customer"
  },
  "authorization": {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "type": "bearer",
    "expires_in": 3600
  }
}
```

### Using the Token

Include the token in all subsequent requests:

```bash
GET /api/v1/products
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGc...
```

## 📚 API Documentation

### Products

#### List Products
```bash
GET /api/v1/products?page=1&search=phone
Authorization: Bearer {token}
```

#### Create Product (Admin/Vendor)
```bash
POST /api/v1/products
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "iPhone 15 Pro",
  "description": "Latest iPhone model",
  "sku": "APPLE-IP15PRO",
  "base_price": 999.99,
  "vendor_id": 2,
  "initial_quantity": 100,
  "low_stock_threshold": 10
}
```

#### Update Product (Admin/Vendor)
```bash
PUT /api/v1/products/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "iPhone 15 Pro Max",
  "base_price": 1199.99,
  "is_active": true
}
```

#### Import Products from CSV (Admin/Vendor)
```bash
POST /api/v1/products/import
Authorization: Bearer {token}
Content-Type: multipart/form-data

file: products.csv
```

**CSV Format:**
```csv
name,sku,base_price,description,initial_quantity,low_stock_threshold
iPhone 15,APPLE-IP15,799.99,Latest iPhone,100,10
Samsung Galaxy S24,SAMSUNG-S24,899.99,Flagship Samsung,50,5
```

### Orders

#### Create Order
```bash
POST /api/v1/orders
Authorization: Bearer {token}
Content-Type: application/json

{
  "items": [
    {
      "product_id": 1,
      "variant_id": null,
      "quantity": 2
    }
  ],
  "shipping_address": "123 Main St, City, State 12345",
  "notes": "Please deliver after 5 PM"
}
```

#### List Orders
```bash
GET /api/v1/orders?status=pending&page=1
Authorization: Bearer {token}
```

#### Get Order Details
```bash
GET /api/v1/orders/{id}
Authorization: Bearer {token}
```

#### Cancel Order
```bash
POST /api/v1/orders/{id}/cancel
Authorization: Bearer {token}
```

#### Update Order Status (Admin/Vendor)
```bash
PATCH /api/v1/orders/{id}/status
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": "processing"
}
```

#### Download Invoice
```bash
GET /api/v1/orders/{id}/invoice
Authorization: Bearer {token}
```

## 🧪 Testing

### Run All Tests

```bash
php artisan test
```

### Run Specific Test Suite

```bash
# Feature tests
php artisan test --testsuite=Feature

# Unit tests
php artisan test --testsuite=Unit

# Specific test file
php artisan test tests/Feature/OrderTest.php
```

### Run with Coverage

```bash
php artisan test --coverage
```

### Test Database

Tests use an in-memory SQLite database by default. Configure in `phpunit.xml`:

```xml
<env name="DB_CONNECTION" value="sqlite"/>
<env name="DB_DATABASE" value=":memory:"/>
```

## 👥 User Roles & Permissions

### Admin
- Full system access
- Manage all products and orders
- Manage users
- View all analytics

### Vendor
- Manage own products
- View orders containing their products
- Update order status
- Import products via CSV

### Customer
- Browse products
- Place orders
- View own order history
- Cancel pending orders

## 🏗️ Architecture

### Repository Pattern

Data access is abstracted through repository interfaces:

```php
app/Repositories/
├── Contracts/
│   ├── ProductRepositoryInterface.php
│   └── OrderRepositoryInterface.php
└── Eloquent/
    ├── ProductRepository.php
    └── OrderRepository.php
```

### Service Layer

Business logic is encapsulated in service classes:

```php
app/Services/
├── OrderService.php
├── ProductService.php
├── InventoryService.php
└── InvoiceService.php
```

### Actions

Complex operations are handled by action classes:

```php
app/Actions/
├── Order/
│   ├── CreateOrderAction.php
│   ├── CancelOrderAction.php
│   └── UpdateOrderStatusAction.php
└── Product/
    └── ImportProductsAction.php
```

### Event-Driven Architecture

Decoupled components using Events & Listeners:

```php
// Events
OrderCreated → [SendOrderConfirmationEmail, GenerateInvoicePdf]
OrderCancelled → [RestoreInventory, NotifyCustomer]
OrderStatusUpdated → [NotifyCustomer]
LowStockDetected → [NotifyAdmin]
```

## 📊 Database Schema

### Key Tables

**users**: id, name, email, password, role, timestamps
**products**: id, vendor_id, name, description, sku, base_price, image_path, is_active, timestamps
**product_variants**: id, product_id, name, sku, price, attributes(json), timestamps
**inventories**: id, product_id, variant_id, quantity, reserved, low_stock_threshold, timestamps
**orders**: id, order_number, user_id, status, subtotal, tax, total_amount, shipping_address, invoice_path, timestamps
**order_items**: id, order_id, product_id, variant_id, quantity, price, subtotal, timestamps
**inventory_logs**: id, inventory_id, type, quantity_before, quantity_after, reference_type, reference_id, timestamps

### Indexes

```sql
-- Products
INDEX idx_products_vendor_created (vendor_id, created_at)
FULLTEXT idx_products_name (name)
FULLTEXT idx_products_description (description)

-- Orders
INDEX idx_orders_user_status_created (user_id, status, created_at)
INDEX idx_orders_status (status)
INDEX idx_orders_order_number (order_number)

-- Inventories
UNIQUE idx_inventories_product_variant (product_id, variant_id)
INDEX idx_inventories_quantity (quantity)
```

## 🔄 Database Sharding Strategy

### Recommended Approach

**Horizontal Sharding by User ID**

1. **Shard Key**: `user_id` (hash-based distribution)
2. **Sharded Tables**: `orders`, `order_items`
3. **Centralized Tables**: `products`, `inventories`, `users`

### Implementation Strategy

```
Shard 1: users with user_id % 4 = 0
Shard 2: users with user_id % 4 = 1
Shard 3: users with user_id % 4 = 2
Shard 4: users with user_id % 4 = 3
```

### Benefits

- Even distribution of order data
- User-specific queries remain on single shard
- Products/inventory remain centralized for consistency
- Can scale horizontally as order volume grows

### Future Considerations

- Implement read replicas for product catalog
- Consider geographic sharding for international expansion
- Use consistent hashing for easier resharding

## ⚡ Performance Optimizations

### Query Optimization

- Eager loading relationships to prevent N+1 queries
- Database indexes on frequently queried columns
- Query result caching for static data

### Caching Strategy

```php
// Cache product catalog (1 hour)
Cache::remember('products.all', 3600, function() {
    return Product::with('inventory')->get();
});

// Cache user orders (5 minutes)
Cache::remember("user.{$userId}.orders", 300, function() use ($userId) {
    return Order::where('user_id', $userId)->get();
});
```

### Queue Jobs

- Email notifications
- PDF invoice generation
- CSV import processing
- Low stock alerts

## 📧 Email Notifications

### Order Confirmation

Sent immediately after order creation:
- Order number and details
- Itemized list
- Total amount
- Estimated delivery

### Order Status Updates

Sent when order status changes:
- New status
- Tracking information (if shipped)
- Expected delivery date

### Low Stock Alerts

Sent to admins when inventory falls below threshold:
- Product details
- Current stock level
- Recommended reorder quantity

## 🔒 Security Features

- JWT token authentication
- Role-based access control
- SQL injection prevention (prepared statements)
- CSRF protection
- Rate limiting on API endpoints
- Password hashing with bcrypt
- Input validation and sanitization

## 🚀 Deployment

### Production Checklist

- [ ] Set `APP_ENV=production` in `.env`
- [ ] Set `APP_DEBUG=false` in `.env`
- [ ] Configure proper database credentials
- [ ] Set up Redis for queue and cache
- [ ] Configure mail server (SMTP/SendGrid/SES)
- [ ] Set up SSL certificate
- [ ] Configure queue workers with Supervisor
- [ ] Set up log rotation
- [ ] Configure backup strategy
- [ ] Set up monitoring (Laravel Telescope/Horizon)

### Queue Workers

Use Supervisor to keep queue workers running:

```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /path/to/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/path/to/worker.log
stopwaitsecs=3600
```

## 📝 API Rate Limiting

Default rate limits:
- 60 requests per minute for authenticated users
- 10 requests per minute for unauthenticated users

Configure in `app/Http/Kernel.php`:

```php
'api' => [
    'throttle:60,1',
    \Illuminate\Routing\Middleware\SubstituteBindings::class,
],
```

## 🐛 Troubleshooting

### Queue Jobs Not Processing

```bash
# Check queue connection
php artisan queue:failed

# Restart queue worker
php artisan queue:restart

# Clear failed jobs
php artisan queue:flush
```

### Search Not Working

```bash
# Re-import data to Meilisearch
php artisan scout:import "App\Models\Product"
```

### JWT Token Issues

```bash
# Regenerate JWT secret
php artisan jwt:secret

# Clear config cache
php artisan config:clear
```

## 👨‍💻 Developer Information

**Name:** [Md.Nazmul Alam]  
**Email:** [nazmul199512@gmail.com]  
**GitHub:** [https://github.com/nazmul199512](https://github.com/nazmul199512)  
**LinkedIn:** [https://linkedin.com/in/nazmul199512/](https://linkedin.com/in/nazmul199512)

## 🤝 Contributing

Pull requests are welcome. For major changes, please open an issue first to discuss what you would like to change.

## 📞 Support

For support, email [your.email@example.com] or create an issue in the GitHub repository.