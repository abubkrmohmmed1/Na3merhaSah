# مشروع "نعمرها صح"

## Overview
مشروع "نعمرها صح" هو نظام وطني متكامل للعنونة الرقمية والبلاغات الهندسية يهدف لتحسين البنية التحتية والخدمات العامة في السودان. يعتمد المشروع على تقنيات حديثة لضمان التوسع الجغرافي ودقة البيانات.

## Tech Stack
* **Backend:** Laravel 12 (Domain-Driven Design).
* **Database:** PostgreSQL with PostGIS (Spatial indexing via S2 Geometry).
* **Frontend:** Flutter (Feature-First Architecture, BLoC State Management).
* **Mapping:** Google Maps API & custom S2 spatial logic.

## Project Structure
* `backend/`: Core logic, API, and Database.
* `frontend/`: Citizen mobile application.

## Development Status
* **Infrastructure:** ✅ Completed.
* **Addressing Logic:** ✅ Completed.
* **Citizen App:** ✅ Completed (UI & Navigation).
* **Workflow:** ✅ Implemented.
* **Admin Dashboard:** 📅 Planned.

## How to Build
* **Backend:** `php artisan serve`
* **Frontend:** `flutter build apk --release` (or `flutter run` for debug)

## Conventions
* Use DDD patterns in the backend.
* Feature-first folder structure in Flutter.
* All database changes require migration files.
