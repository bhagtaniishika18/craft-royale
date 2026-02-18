# Data Flow Diagrams (DFD) - Craft Royale E-Commerce Platform

## Context Diagram (Level 0 DFD)

The context diagram shows the entire system as a single process with external entities.

```mermaid
graph TB
    Customer([Customer])
    Admin([Administrator])
    EmailSystem([Email System])
    PaymentGateway([Payment Gateway])
    
    System[Craft Royale<br/>E-Commerce System]
    
    Customer -->|Product Requests| System
    Customer -->|Order Details| System
    Customer -->|Contact Inquiry| System
    Customer -->|Login Credentials| System
    
    System -->|Product Information| Customer
    System -->|Order Confirmation| Customer
    System -->|Receipt/Invoice| Customer
    System -->|Cart Updates| Customer
    
    Admin -->|Product Data| System
    Admin -->|Login Credentials| System
    Admin -->|Order Management| System
    
    System -->|Sales Reports| Admin
    System -->|Order Details| Admin
    System -->|Contact Inquiries| Admin
    
    System -->|Email Notifications| EmailSystem
    EmailSystem -->|Delivery Status| System
    
    System -->|Payment Request| PaymentGateway
    PaymentGateway -->|Payment Confirmation| System
    
    style System fill:#4CAF50,stroke:#2E7D32,stroke-width:3px,color:#fff
    style Customer fill:#2196F3,stroke:#1565C0,stroke-width:2px,color:#fff
    style Admin fill:#FF9800,stroke:#E65100,stroke-width:2px,color:#fff
    style EmailSystem fill:#9C27B0,stroke:#6A1B9A,stroke-width:2px,color:#fff
    style PaymentGateway fill:#F44336,stroke:#C62828,stroke-width:2px,color:#fff
```

---

## Level 1 DFD

The Level 1 DFD breaks down the system into major processes.

```mermaid
graph TB
    Customer([Customer])
    Admin([Administrator])
    
    P1[1.0<br/>Product<br/>Management]
    P2[2.0<br/>Shopping Cart<br/>Management]
    P3[3.0<br/>Order<br/>Processing]
    P4[4.0<br/>User<br/>Authentication]
    P5[5.0<br/>Contact<br/>Management]
    P6[6.0<br/>Receipt<br/>Generation]
    
    D1[(Product<br/>Database)]
    D2[(Cart<br/>Database)]
    D3[(Order<br/>Database)]
    D4[(User<br/>Database)]
    D5[(Contact<br/>Database)]
    
    Customer -->|Browse Products| P1
    Customer -->|Add/Update Cart| P2
    Customer -->|Place Order| P3
    Customer -->|Login/Register| P4
    Customer -->|Submit Inquiry| P5
    
    P1 -->|Product Details| Customer
    P2 -->|Cart Summary| Customer
    P3 -->|Order Confirmation| Customer
    P4 -->|Authentication Status| Customer
    P6 -->|Receipt/PDF| Customer
    
    Admin -->|Manage Products| P1
    Admin -->|View Orders| P3
    Admin -->|Admin Login| P4
    Admin -->|View Inquiries| P5
    
    P1 <-->|Product Data| D1
    P2 <-->|Cart Data| D2
    P3 <-->|Order Data| D3
    P4 <-->|User Data| D4
    P5 <-->|Contact Data| D5
    
    P2 -->|Cart Items| P3
    P3 -->|Order Details| P6
    P4 -->|User ID| P2
    P4 -->|User ID| P3
    
    style P1 fill:#4CAF50,stroke:#2E7D32,stroke-width:2px,color:#fff
    style P2 fill:#2196F3,stroke:#1565C0,stroke-width:2px,color:#fff
    style P3 fill:#FF9800,stroke:#E65100,stroke-width:2px,color:#fff
    style P4 fill:#9C27B0,stroke:#6A1B9A,stroke-width:2px,color:#fff
    style P5 fill:#F44336,stroke:#C62828,stroke-width:2px,color:#fff
    style P6 fill:#00BCD4,stroke:#006064,stroke-width:2px,color:#fff
    style D1 fill:#FFF9C4,stroke:#F57F17,stroke-width:2px
    style D2 fill:#FFF9C4,stroke:#F57F17,stroke-width:2px
    style D3 fill:#FFF9C4,stroke:#F57F17,stroke-width:2px
    style D4 fill:#FFF9C4,stroke:#F57F17,stroke-width:2px
    style D5 fill:#FFF9C4,stroke:#F57F17,stroke-width:2px
```

---

## Level 2 DFD - Product Management (Process 1.0)

```mermaid
graph TB
    Customer([Customer])
    Admin([Administrator])
    
    P11[1.1<br/>Browse<br/>Products]
    P12[1.2<br/>Search<br/>Products]
    P13[1.3<br/>View Product<br/>Details]
    P14[1.4<br/>Add/Edit<br/>Product]
    P15[1.5<br/>Delete<br/>Product]
    
    D1[(Product<br/>Database)]
    D6[(Category<br/>Database)]
    
    Customer -->|Browse Request| P11
    Customer -->|Search Query| P12
    Customer -->|Product ID| P13
    
    P11 -->|Product List| Customer
    P12 -->|Search Results| Customer
    P13 -->|Product Details| Customer
    
    Admin -->|New Product Data| P14
    Admin -->|Update Product Data| P14
    Admin -->|Delete Request| P15
    
    P11 <-->|Read Products| D1
    P12 <-->|Query Products| D1
    P13 <-->|Read Details| D1
    P14 <-->|Write/Update| D1
    P15 <-->|Delete Record| D1
    
    P11 <-->|Category Filter| D6
    P12 <-->|Category Data| D6
    
    style P11 fill:#4CAF50,stroke:#2E7D32,stroke-width:2px,color:#fff
    style P12 fill:#4CAF50,stroke:#2E7D32,stroke-width:2px,color:#fff
    style P13 fill:#4CAF50,stroke:#2E7D32,stroke-width:2px,color:#fff
    style P14 fill:#FF9800,stroke:#E65100,stroke-width:2px,color:#fff
    style P15 fill:#F44336,stroke:#C62828,stroke-width:2px,color:#fff
    style D1 fill:#FFF9C4,stroke:#F57F17,stroke-width:2px
    style D6 fill:#FFF9C4,stroke:#F57F17,stroke-width:2px
```

---

## Level 2 DFD - Shopping Cart Management (Process 2.0)

```mermaid
graph TB
    Customer([Customer])
    
    P21[2.1<br/>Add Item<br/>to Cart]
    P22[2.2<br/>Update<br/>Quantity]
    P23[2.3<br/>Remove<br/>Item]
    P24[2.4<br/>Calculate<br/>Totals]
    P25[2.5<br/>View<br/>Cart]
    
    D1[(Product<br/>Database)]
    D2[(Cart<br/>Database)]
    D7[(Cart Items<br/>Database)]
    
    Customer -->|Product ID, Quantity| P21
    Customer -->|Item ID, New Quantity| P22
    Customer -->|Item ID| P23
    Customer -->|View Request| P25
    
    P21 -->|Success Message| Customer
    P22 -->|Updated Cart| Customer
    P23 -->|Updated Cart| Customer
    P25 -->|Cart Details| Customer
    
    P21 -->|Product Info Request| D1
    D1 -->|Price, Stock| P21
    
    P21 -->|New Cart Item| D7
    P22 <-->|Update Item| D7
    P23 -->|Delete Item| D7
    
    D7 -->|Cart Items| P24
    P24 -->|Subtotal, Tax, Total| D2
    
    P25 <-->|Read Cart Data| D2
    P25 <-->|Read Items| D7
    
    P24 -->|Calculated Totals| Customer
    
    style P21 fill:#2196F3,stroke:#1565C0,stroke-width:2px,color:#fff
    style P22 fill:#2196F3,stroke:#1565C0,stroke-width:2px,color:#fff
    style P23 fill:#F44336,stroke:#C62828,stroke-width:2px,color:#fff
    style P24 fill:#4CAF50,stroke:#2E7D32,stroke-width:2px,color:#fff
    style P25 fill:#2196F3,stroke:#1565C0,stroke-width:2px,color:#fff
    style D1 fill:#FFF9C4,stroke:#F57F17,stroke-width:2px
    style D2 fill:#FFF9C4,stroke:#F57F17,stroke-width:2px
    style D7 fill:#FFF9C4,stroke:#F57F17,stroke-width:2px
```

---

## Level 2 DFD - Order Processing (Process 3.0)

```mermaid
graph TB
    Customer([Customer])
    Admin([Administrator])
    Email([Email System])
    
    P31[3.1<br/>Validate<br/>Order]
    P32[3.2<br/>Create<br/>Order]
    P33[3.3<br/>Process<br/>Payment]
    P34[3.4<br/>Update<br/>Inventory]
    P35[3.5<br/>Send<br/>Confirmation]
    P36[3.6<br/>Manage<br/>Orders]
    
    D2[(Cart<br/>Database)]
    D3[(Order<br/>Database)]
    D8[(Order Items<br/>Database)]
    D1[(Product<br/>Database)]
    
    Customer -->|Checkout Request| P31
    P31 -->|Validation Result| Customer
    
    P31 <-->|Read Cart| D2
    P31 -->|Valid Order Data| P32
    
    P32 -->|Order Details| P33
    P32 -->|Create Order Record| D3
    P32 -->|Create Order Items| D8
    
    P33 -->|Payment Status| P34
    P33 -->|Payment Confirmation| Customer
    
    P34 <-->|Update Stock| D1
    P34 -->|Inventory Updated| P35
    
    P35 -->|Order Confirmation| Email
    Email -->|Email Sent| Customer
    
    P35 <-->|Order Details| D3
    
    Admin -->|View/Update Orders| P36
    P36 <-->|Order Data| D3
    P36 <-->|Order Items| D8
    P36 -->|Order Status| Admin
    
    style P31 fill:#FF9800,stroke:#E65100,stroke-width:2px,color:#fff
    style P32 fill:#4CAF50,stroke:#2E7D32,stroke-width:2px,color:#fff
    style P33 fill:#2196F3,stroke:#1565C0,stroke-width:2px,color:#fff
    style P34 fill:#9C27B0,stroke:#6A1B9A,stroke-width:2px,color:#fff
    style P35 fill:#00BCD4,stroke:#006064,stroke-width:2px,color:#fff
    style P36 fill:#FF9800,stroke:#E65100,stroke-width:2px,color:#fff
    style D1 fill:#FFF9C4,stroke:#F57F17,stroke-width:2px
    style D2 fill:#FFF9C4,stroke:#F57F17,stroke-width:2px
    style D3 fill:#FFF9C4,stroke:#F57F17,stroke-width:2px
    style D8 fill:#FFF9C4,stroke:#F57F17,stroke-width:2px
```

---

## Level 2 DFD - User Authentication (Process 4.0)

```mermaid
graph TB
    Customer([Customer])
    Admin([Administrator])
    
    P41[4.1<br/>User<br/>Registration]
    P42[4.2<br/>User<br/>Login]
    P43[4.3<br/>Session<br/>Management]
    P44[4.4<br/>Password<br/>Validation]
    P45[4.5<br/>Logout]
    
    D4[(User<br/>Database)]
    D9[(Session<br/>Database)]
    
    Customer -->|Registration Data| P41
    Customer -->|Login Credentials| P42
    Admin -->|Admin Credentials| P42
    
    P41 -->|Validate Data| P44
    P44 -->|Validation Result| P41
    P41 -->|Store User| D4
    P41 -->|Registration Success| Customer
    
    P42 <-->|Verify Credentials| D4
    P42 -->|Create Session| P43
    P43 -->|Session Token| D9
    P43 -->|Login Success| Customer
    P43 -->|Login Success| Admin
    
    Customer -->|Logout Request| P45
    Admin -->|Logout Request| P45
    P45 -->|Destroy Session| D9
    P45 -->|Logout Confirmation| Customer
    P45 -->|Logout Confirmation| Admin
    
    style P41 fill:#4CAF50,stroke:#2E7D32,stroke-width:2px,color:#fff
    style P42 fill:#2196F3,stroke:#1565C0,stroke-width:2px,color:#fff
    style P43 fill:#9C27B0,stroke:#6A1B9A,stroke-width:2px,color:#fff
    style P44 fill:#FF9800,stroke:#E65100,stroke-width:2px,color:#fff
    style P45 fill:#F44336,stroke:#C62828,stroke-width:2px,color:#fff
    style D4 fill:#FFF9C4,stroke:#F57F17,stroke-width:2px
    style D9 fill:#FFF9C4,stroke:#F57F17,stroke-width:2px
```

---

## Level 2 DFD - Contact Management (Process 5.0)

```mermaid
graph TB
    Customer([Customer])
    Admin([Administrator])
    Email([Email System])
    
    P51[5.1<br/>Submit<br/>Contact Form]
    P52[5.2<br/>Validate<br/>Input]
    P53[5.3<br/>Store<br/>Inquiry]
    P54[5.4<br/>View<br/>Inquiries]
    P55[5.5<br/>Send<br/>Notification]
    P56[5.6<br/>Update<br/>Status]
    
    D5[(Contact<br/>Database)]
    
    Customer -->|Contact Form Data| P51
    P51 -->|Form Data| P52
    P52 -->|Validation Errors| Customer
    P52 -->|Valid Data| P53
    
    P53 -->|Store Contact| D5
    P53 -->|Trigger Notification| P55
    P53 -->|Confirmation| Customer
    
    P55 -->|Admin Notification| Email
    Email -->|Notification Sent| Admin
    
    Admin -->|View Request| P54
    P54 <-->|Read Contacts| D5
    P54 -->|Contact List| Admin
    
    Admin -->|Update Status| P56
    P56 <-->|Update Record| D5
    P56 -->|Status Updated| Admin
    
    style P51 fill:#2196F3,stroke:#1565C0,stroke-width:2px,color:#fff
    style P52 fill:#FF9800,stroke:#E65100,stroke-width:2px,color:#fff
    style P53 fill:#4CAF50,stroke:#2E7D32,stroke-width:2px,color:#fff
    style P54 fill:#9C27B0,stroke:#6A1B9A,stroke-width:2px,color:#fff
    style P55 fill:#00BCD4,stroke:#006064,stroke-width:2px,color:#fff
    style P56 fill:#FF9800,stroke:#E65100,stroke-width:2px,color:#fff
    style D5 fill:#FFF9C4,stroke:#F57F17,stroke-width:2px
```

---

## Level 2 DFD - Receipt Generation (Process 6.0)

```mermaid
graph TB
    Customer([Customer])
    
    P61[6.1<br/>Retrieve<br/>Order Data]
    P62[6.2<br/>Format<br/>Receipt]
    P63[6.3<br/>Generate<br/>Web Receipt]
    P64[6.4<br/>Generate<br/>PDF Receipt]
    P65[6.5<br/>Send Email<br/>Receipt]
    
    D3[(Order<br/>Database)]
    D8[(Order Items<br/>Database)]
    D4[(User<br/>Database)]
    Email([Email System])
    
    Customer -->|Order ID| P61
    P61 <-->|Read Order| D3
    P61 <-->|Read Items| D8
    P61 <-->|Read User Info| D4
    
    P61 -->|Order Details| P62
    P62 -->|Formatted Data| P63
    P62 -->|Formatted Data| P64
    P62 -->|Formatted Data| P65
    
    P63 -->|HTML Receipt| Customer
    P64 -->|PDF Download| Customer
    
    P65 -->|Email with Receipt| Email
    Email -->|Email Delivered| Customer
    
    style P61 fill:#2196F3,stroke:#1565C0,stroke-width:2px,color:#fff
    style P62 fill:#4CAF50,stroke:#2E7D32,stroke-width:2px,color:#fff
    style P63 fill:#9C27B0,stroke:#6A1B9A,stroke-width:2px,color:#fff
    style P64 fill:#FF9800,stroke:#E65100,stroke-width:2px,color:#fff
    style P65 fill:#00BCD4,stroke:#006064,stroke-width:2px,color:#fff
    style D3 fill:#FFF9C4,stroke:#F57F17,stroke-width:2px
    style D8 fill:#FFF9C4,stroke:#F57F17,stroke-width:2px
    style D4 fill:#FFF9C4,stroke:#F57F17,stroke-width:2px
```

---

## Data Store Details

| Data Store | Description | Key Fields |
|------------|-------------|------------|
| D1 - Product Database | Stores all product information | product_id, name, description, price, image_url, stock_quantity, category |
| D2 - Cart Database | Stores shopping cart summary | cart_id, user_id, subtotal, tax, total, updated_at |
| D3 - Order Database | Stores order information | order_id, user_id, order_date, total_amount, status, shipping_address |
| D4 - User Database | Stores user account information | user_id, username, email, password_hash, phone, created_at |
| D5 - Contact Database | Stores customer inquiries | contact_id, name, email, subject, message, submitted_date, status |
| D6 - Category Database | Stores product categories | category_id, category_name, description |
| D7 - Cart Items Database | Stores individual cart items | cart_item_id, cart_id, product_id, quantity, unit_price, subtotal |
| D8 - Order Items Database | Stores individual order items | order_item_id, order_id, product_id, quantity, unit_price, subtotal |
| D9 - Session Database | Stores user session data | session_id, user_id, session_token, created_at, expires_at |

---

## External Entities

| Entity | Description | Interactions |
|--------|-------------|--------------|
| Customer | End users who browse and purchase products | Browse products, manage cart, place orders, submit inquiries |
| Administrator | System admin who manages the platform | Manage products, process orders, view inquiries, generate reports |
| Email System | External email service for notifications | Send order confirmations, contact notifications, receipts |
| Payment Gateway | External payment processing service | Process payments, return transaction status |

---

## Process Summary

| Process ID | Process Name | Description |
|------------|--------------|-------------|
| 1.0 | Product Management | Handles product catalog operations including browsing, searching, and admin management |
| 2.0 | Shopping Cart Management | Manages cart operations including add, update, remove, and calculation |
| 3.0 | Order Processing | Handles order creation, payment, inventory updates, and confirmations |
| 4.0 | User Authentication | Manages user registration, login, session management, and logout |
| 5.0 | Contact Management | Handles customer inquiries and admin responses |
| 6.0 | Receipt Generation | Generates order receipts in web and PDF formats |

---

**Note:** These DFD diagrams are created using Mermaid syntax and will render as visual diagrams in Markdown viewers that support Mermaid (GitHub, GitLab, VS Code with extensions, etc.).
