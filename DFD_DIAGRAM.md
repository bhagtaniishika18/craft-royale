# Data Flow Diagram (DFD) - Craft Royale

This document provides a high-level view of how information moves through the Craft Royale platform at different abstraction levels.

## Level 0: Context Diagram
The context diagram shows the entire system as a single process and its interactions with external entities.

```mermaid
graph LR
    Customer((Customer / Guest))
    Admin((Administrator))
    PaymentSys([Payment Gateway])
    EmailSys([Email Service])
    System(Craft Royale E-Commerce System)

    %% Flows from Customer
    Customer -- Search/Browse/Query --> System
    Customer -- Order Details/Payment --> System
    Customer -- Account Info --> System
    
    %% Flows to Customer
    System -- Product Details --> Customer
    System -- Receipts / Order Status --> Customer
    System -- Support Responses --> Customer

    %% Flows from Admin
    Admin -- Product/Category Info --> System
    Admin -- Status Updates --> System
    
    %% Flows to Admin
    System -- Sales Reports --> Admin
    System -- Support Inquiries --> Admin

    %% System interactions
    System -- Transaction Details --> PaymentSys
    PaymentSys -- Success/Fail Status --> System
    System -- Email Data --> EmailSys
```

---

## Level 1: Functional DFD
The Level 1 DFD breaks down the main system into its primary processes and core data stores.

```mermaid
graph TB
    %% External Entities
    Customer((Customer))
    Admin((Administrator))

    %% Data Stores
    D1[(D1: Products & Categories)]
    D2[(D2: Users & Profiles)]
    D3[(D3: Orders & Receipts)]
    D4[(D4: Support & Callbacks)]

    %% Processes
    P1(1.0 Browsing & Search)
    P2(2.0 Order & Checkout)
    P3(3.0 User Management)
    P4(4.0 Support & Callbacks)
    P5(5.0 Admin Control)

    %% P1 Flows
    Customer -- Query --> P1
    P1 -- Product Info --> Customer
    D1 -- Catalog Data --> P1

    %% P2 Flows
    Customer -- Order Request --> P2
    P2 -- Receipt/Invoice --> Customer
    P2 -- Update Orders --> D3
    D1 -- Inventory Check --> P2

    %% P3 Flows
    Customer -- credentials --> P3
    P3 -- Profile Data --> Customer
    P3 -- CRUD Users --> D2

    %% P4 Flows
    Customer -- Callback/Query --> P4
    P4 -- Resolution --> Customer
    P4 -- Store Messaging --> D4

    %% P5 Flows
    Admin -- Catalog Update --> P5
    P5 -- Write DB --> D1
    Admin -- Process Orders --> P5
    P5 -- Update Status --> D3
    Admin -- View Support --> P5
    D4 -- Message Data --> P5
```

---

## Main Data Flow Topics

### 1. Catalog Flow (P1 & P5)
Manages how product data is added by admins (D1) and retrieved by customers for browsing.

### 2. Transaction Flow (P2)
Handles the critical path from cart to order creation (D3), involving inventory checks and receipt generation.

### 3. Identity Flow (P3)
The movement of user credentials and profile updates between the user and the secure user store (D2).

### 4. Communication Flow (P4)
Manages support tickets and the callback request system, tracking communication between customers and admins (D4).

---
**Note:** This DFD follows the Gane-Sarson notation logic using Mermaid for architectural visualization.
