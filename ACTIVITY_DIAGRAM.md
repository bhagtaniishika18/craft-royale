# Activity Diagrams - Craft Royale

This document illustrates the step-by-step workflow of the core processes within the Craft Royale platform.

## 1. Purchase & Checkout Workflow
This diagram follows the activity from a user arriving at the site to successfully placing an order.

```mermaid
graph TD
    Start([User Arrives]) --> Browse[Browse/Search Products]
    Browse --> ViewProduct[View Product Details]
    ViewProduct --> AddToCart[Add to Shopping Cart]
    
    AddToCart --> ReviewCart{Review Cart?}
    ReviewCart -- Modify --> UpdateQty[Update Quantity/Remove]
    UpdateQty --> ReviewCart
    ReviewCart -- Proceed --> CheckLogin{Is User Logged In?}
    
    CheckLogin -- No --> LoginReg[Login / Register]
    LoginReg --> CheckLogin
    CheckLogin -- Yes --> Shipping[Enter Shipping Details]
    
    Shipping --> Payment[Select & Process Payment]
    Payment --> Validate{Payment Success?}
    Validate -- No --> Error[Display Error]
    Error --> Payment
    Validate -- Yes --> PlaceOrder[Create Order & Inventory Update]
    
    PlaceOrder --> Receipt[Generate Receipt & Email]
    Receipt --> End([Order Confirmed])
```

---

## 2. Callback Request Workflow
This illustrates the activity flow for the support feature recently implemented.

```mermaid
graph TD
    StartCB([Needs Help]) --> ContactPage[Visit Contact Page]
    ContactPage --> TriggerPopup[Click 'Request a Call']
    TriggerPopup --> EnterDetails[Enter Name & Phone]
    EnterDetails --> Submit[Submit AJAX Request]
    
    Submit --> SaveDB[Store in callback_requests Table]
    SaveDB --> UserStatus[View Status on Profile]
    
    subgraph "Admin Activity"
        AdminCheck[Admin Views Callback List] --> ProcessCall[Call Customer & Discuss]
        ProcessCall --> UpdateAdmin[Update Status & Add Notes]
    end
    
    UpdateAdmin --> UserStatus
    UserStatus --> EndCB([Issue Resolved])
```

---

## Main Activity Topics

### 1. The Sales Loop
Focuses on the conversion of a visitor into a customer by providing a seamless path through product discovery and secure checkout.

### 2. The Support Loop
Ensures that users can easily reach out for personalized assistance and track their request status, closing the loop between the customer and the admin.

---
**Note:** These diagrams use Gane-Sarson logic to represent the flow of control within the Craft Royale system.
