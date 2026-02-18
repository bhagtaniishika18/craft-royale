# High-Level Class Diagram - Craft Royale

This diagram represents the core logical structure and relationships between the main entities of the Craft Royale E-Commerce platform.

```mermaid
classDiagram
    class User {
        +int id
        +string name
        +string email
        +string mobile_no
        +string password
        +register()
        +login()
        +updateProfile()
    }

    class Product {
        +int id
        +string name
        +string sku
        +double price
        +int stock
        +string image
        +viewDetails()
    }

    class Category {
        +int id
        +string name
        +string slug
    }

    class Order {
        +int id
        +int user_id
        +double total_amount
        +string status
        +datetime created_at
        +placeOrder()
        +updateStatus()
    }

    class OrderItem {
        +int id
        +int order_id
        +int product_id
        +int quantity
        +double subtotal
    }

    class Cart {
        +list items
        +addItem()
        +updateQuantity()
        +calculateTotal()
    }

    class CallbackRequest {
        +int id
        +int user_id
        +string phone
        +string status
        +string admin_note
        +submitRequest()
        +updateStatus()
    }

    class ContactMessage {
        +int id
        +string name
        +string email
        +string message
        +submitQuery()
    }

    %% Relationships
    User "1" -- "0..*" Order : places
    User "1" -- "0..*" CallbackRequest : requests
    Order "1" -- "1..*" OrderItem : contains
    Product "1" -- "0..*" OrderItem : ordered_as
    Category "1" -- "0..*" Product : contains
    User "1" -- "1" Cart : manages
    OrderItem "*" -- "1" Product : references
```

---

## Core Logical Entities

### 1. User & Account
Handles identity and profile management. Users are the central actors who interact with orders and support features.

### 2. Product & Catalog
Organizes the items for sale. Products are categorized into groups to help users discover them easily.

### 3. Order & Transactions
Manages the purchase flow. An **Order** acts as a container for multiple **OrderItems**, representing a finalized transaction between the user and the store.

### 4. Communication (Support)
Includes **CallbackRequest** and **ContactMessage**. These classes manage the interaction between users and administrators for help and guidance.

---
**Note:** This is a conceptual class diagram focusing on the main system topics for architectural clarity.
