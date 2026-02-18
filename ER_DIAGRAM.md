# Entity-Relationship (ER) Diagram - Craft Royale

This diagram illustrates the data models and the relationships between various entities in the Craft Royale database.

```mermaid
erDiagram
    USERS ||--o{ ORDERS : places
    USERS ||--o{ CALLBACK_REQUESTS : requests
    USERS ||--o{ CONTACT_MESSAGES : submits
    
    CATEGORIES ||--o{ PRODUCTS : categorizes
    
    ORDERS ||--|{ ORDER_ITEMS : contains
    PRODUCTS ||--o{ ORDER_ITEMS : "ordered as"

    USERS {
        int id PK
        string name
        string email
        string mobile_no
        string password
    }

    PRODUCTS {
        int id PK
        int category_id FK
        string name
        string sku
        double price
        int stock
        string image
    }

    CATEGORIES {
        int id PK
        string category_name
        string category_slug
    }

    ORDERS {
        int id PK
        int user_id FK
        double total_amount
        string status
        timestamp created_at
    }

    ORDER_ITEMS {
        int id PK
        int order_id FK
        int product_id FK
        int quantity
        double subtotal
    }

    CALLBACK_REQUESTS {
        int id PK
        int user_id FK
        string phone
        string status
        text admin_note
        timestamp created_at
    }

    CONTACT_MESSAGES {
        int id PK
        string name
        string email
        text message
        timestamp created_at
    }
```

---

## Core Database Entities

### 1. Account Entities
- **USERS**: Stores profile and authentication data for customers and administrators.
- **CALLBACK_REQUESTS**: Stores phone support requests, linked to users for status tracking.

### 2. Catalog Entities
- **CATEGORIES**: High-level groupings (e.g., Embroidery, Beads).
- **PRODUCTS**: Individual inventory items linked to specific categories.

### 3. Transactional Entities
- **ORDERS**: High-level record of a purchase, including status and totals.
- **ORDER_ITEMS**: Linking table that connects products to specific orders, including quantity and price at the time of purchase.

---
**Note:** Primary Keys (PK) and Foreign Keys (FK) are indicated to show how the relational data is indexed and linked across the system.
