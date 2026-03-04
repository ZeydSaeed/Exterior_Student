# Prompt Engineering Guide for Strict Software Architecture

## 🎯 Purpose

This document is a **system-level architectural rule file** used with Cursor or any AI coding assistant.
It forces all code generation and modifications to strictly follow the defined architecture and design principles.

The AI must:

* Read this file before making changes
* Follow it without exception
* Never break architectural boundaries

---

# 1️⃣ Technology Stack

The project must use:

* Laravel (latest stable version)
* PHP (strict OOP)
* PostgreSQL
* HTML
* CSS
* JavaScript
* Offline-first development (no external runtime dependencies)

---

# 2️⃣ Architecture Rules

The project must follow:

## ✔ Clean Architecture

* Independent of frameworks
* Business logic in the Domain layer
* No framework code inside Domain

## ✔ MVC (Presentation Layer Only)

* Controllers: thin
* Views: display only
* No business logic in views

## ✔ Layered Architecture

Layers order:

1. Presentation (Controllers / Views)
2. Application (Use Cases)
3. Domain (Entities / Business Rules)
4. Infrastructure (Database / External Services)

Dependencies flow ONLY inward.

---

# 3️⃣ Domain-Driven Design (DDD)

Must include:

* Entities
* Value Objects
* Aggregates
* Repositories (interfaces in Domain)
* Use Cases (Application layer)

Business rules MUST live inside Domain.

---

# 4️⃣ SOLID Principles

All code must follow:

* S: Single Responsibility
* O: Open/Closed
* L: Liskov Substitution
* I: Interface Segregation
* D: Dependency Inversion

No class should have more than one responsibility.

---

# 5️⃣ Design Principles

* DRY (Don’t Repeat Yourself)
* KISS (Keep It Simple)
* YAGNI (You Aren’t Gonna Need It)
* High Cohesion
* Low Coupling

---

# 6️⃣ Design Patterns (Allowed)

* Repository Pattern
* Service Layer
* Factory Pattern
* DTO Pattern
* Dependency Injection
* Strategy Pattern (when needed)

No unnecessary patterns.

---

# 7️⃣ CQRS (If Used)

If implemented:

* Separate Commands and Queries
* Write models separated from read models
* No mixing of responsibilities

---

# 8️⃣ Database Rules

## PostgreSQL Only

Must follow:

* Proper indexing
* Foreign keys
* Constraints
* Normalization

## ACID Compliance

* Atomicity
* Consistency
* Isolation
* Durability

Transactions must be used for critical operations.

---

# 9️⃣ Code Boundaries (STRICT)

❌ No business logic inside:

* Controllers
* Views
* Routes

❌ No direct database queries inside Domain

❌ No Eloquent models inside Domain

✔ Infrastructure handles database
✔ Domain defines interfaces

---

# 🔟 Offline Rule

* No external APIs unless explicitly allowed
* No cloud dependencies
* System must run locally

---

# 1️⃣1️⃣ AI Behavior Rules (Very Important)

When using Cursor or any AI:

The AI must:

* Read this file before editing
* Respect folder structure
* Never change architecture style
* Never mix layers
* Never bypass Domain rules

If a request violates architecture:

* The AI must refuse and suggest a compliant solution.

---

# 1️⃣2️⃣ Required Project Structure

Example:

app/
├── Domain/
├── Application/
├── Infrastructure/
├── Presentation/

Domain must be framework-independent.

---

# 1️⃣3️⃣ Development Policy

Every new feature must:

1. Start from Domain
2. Define rules
3. Add Use Case
4. Implement Infrastructure
5. Connect via Controller

Never start from Controller.

---

# 📌 Final Rule

This document is the single source of truth.

Any generated code must comply 100%.
No exceptions.
