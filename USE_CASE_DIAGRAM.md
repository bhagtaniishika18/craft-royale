# Simplified Use Case Diagram - Craft Royale

This document provides a high-level overview of the main functional topics of the Craft Royale E-commerce platform.

## High-Level Use Case Diagram

```mermaid
graph LR
    Customer((Customer))
    Admin((Administrator))
    Guest((Guest User))
    
    subgraph "Craft Royale E-Commerce Platform"
        subgraph "Shopping & Discovery"
            UC1[Browse & Search Products]
            UC2[Manage Shopping Cart]
            UC3[Checkout & Place Order]
            UC4[Track Order Status]
        end
        
        subgraph "User Account"
            UC5[Register & Login]
            UC6[Manage User Profile]
            UC7[View Order History]
        end
        
        subgraph "Admin Management"
            UC8[Manage Product Catalog]
            UC9[Process Customer Orders]
            UC10[Generate Sales Reports]
        end
        
        subgraph "Support & Communication"
            UC11[Submit Contact Inquiries]
            UC12[Request & Track Callbacks]
            UC13[Resolve Support Tickets]
        end
    end
    
    %% Relationships
    Guest --> UC1
    Guest --> UC5
    Guest --> UC11
    Guest --> UC12
    
    Customer --> UC1
    Customer --> UC2
    Customer --> UC3
    Customer --> UC4
    Customer --> UC6
    Customer --> UC7
    Customer --> UC11
    Customer --> UC12
    
    Admin --> UC8
    Admin --> UC9
    Admin --> UC10
    Admin --> UC13
```

---

## Main Functional Topics

### 1. Shopping & Discovery
The core e-commerce flow allowing users to find products, manage their cart, and securely place orders.

### 2. User Account
Personalized features for registered users to manage their profiles, identities, and see their past activity.

### 3. Admin Management
Deep-level control for store owners to manage inventory, fulfill orders, and monitor business performance.

### 4. Support & Communication
Direct channels for customers to get help through contact forms and the new callback request system.

---
**Note:** This is a simplified view intended for university reports and high-level architectural overviews.
