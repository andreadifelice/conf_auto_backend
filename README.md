# Progetto backend: configuratore auto

## Configurazione connessione del database
Rinomia il file `.env.example` in `.env` e inserisci i dati necessari per collegare il DB al backend.

## Comandi Utili

```bash
# Avvia server di sviluppo (PHP built-in)
php -S localhost:8000 -t public
```

## 1. Architettura del progetto

Il backend è sviluppato in **PHP 8.3+** con **Laravel 13**, seguendo un'architettura MVC orientata alle API REST in formato JSON.

L'obiettivo principale è gestire il **catalogo veicoli** (modelli, motori, colori, optional) e permettere agli utenti autenticati di **salvare configurazioni personalizzate** e scaricare un **preventivo in PDF**. Gli amministratori gestiscono l'intero catalogo tramite endpoint protetti.

## Componenti principali:
- **Laravel Sanctum** — autenticazione tramite token API
- **Eloquent ORM** — modelli e relazioni tra entità
- **Form Request** — validazione centralizzata degli input
- **DomPDF** — generazione del preventivo PDF
- **Mail** — invio OTP per la verifica email e link per il reset password

## 2. Struttura del database

Il sistema utilizza **PostgreSQL** come motore di database. Lo schema è organizzato attorno al catalogo auto e alle configurazioni salvate dagli utenti.

### Tabelle principali

| Tabella | Descrizione |
|---------|-------------|
| `users` | Utenti dell'applicazione (`role`: `user` o `admin`), con campi OTP per la verifica email |
| `categories` | Categorie di veicoli (nome, slug) |
| `car_models` | Modelli auto con prezzo base, anno, descrizione e stato attivo/inattivo |
| `car_model_images` | Immagini associate a ciascun modello |
| `engines` | Motori disponibili con tipo carburante, cavalli e prezzo aggiuntivo |
| `colors` | Colori disponibili con codice esadecimale |
| `optionals` | Optional e accessori con categoria e prezzo |
| `configurations` | Configurazioni salvate dagli utenti con prezzo totale e stato |

### Tabelle di relazione (molti-a-molti)

| Tabella | Collega |
|---------|---------|
| `car_model_engine` | Modelli ↔ motori disponibili per ciascun modello |
| `car_model_optional` | Modelli ↔ optional disponibili per ciascun modello |
| `car_color` | Modelli ↔ colori, con `price_surcharge` per ogni combinazione |
| `configuration_optional` | Configurazioni ↔ optional selezionati |
| `optional_compatibilities` | Regole tra optional (`requires_optional_id`, `excludes_optional_id`) |

### Vincoli di integrità

- **Chiavi esterne** su tutte le relazioni principali (`category_id`, `car_model_id`, `engine_id`, `color_id`, `user_id`, ecc.)
- **Un modello non può essere eliminato** se è ancora referenziato da una o più configurazioni
- **Il colore in una configurazione** deve essere tra quelli disponibili per il modello scelto (validato anche a livello applicativo)
- **Compatibilità optional:** un optional può richiedere (`requires`) o escludere (`excludes`) un altro optional
- **Cascade on delete** su `car_color` e `car_model_images` quando viene rimosso un modello

## 3. Struttura del Progetto

```
conf_auto_backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php                  # Registrazione, login, logout
│   │   │   ├── CarModelController.php              # Catalogo modelli auto
│   │   │   ├── CarModelEngineController.php        # Associazioni modello–motore
│   │   │   ├── CarModelOptionalController.php      # Associazioni modello–optional
│   │   │   ├── CarColorController.php              # Associazioni modello–colore
│   │   │   ├── CategoryController.php              # Categorie veicoli
│   │   │   ├── ColorController.php                 # Colori
│   │   │   ├── ConfigurationController.php         # Salvataggio configurazioni e PDF
│   │   │   ├── EmailVerificationController.php     # Verifica email via OTP
│   │   │   ├── EngineController.php                # Motori
│   │   │   ├── OptionalController.php              # Optional
│   │   │   ├── OptionalCompatibilityController.php # Regole requires/excludes
│   │   │   ├── PasswordResetController.php         # Reset password
│   │   │   └── UserController.php                  # Gestione utenti
│   │   ├── Middleware/
│   │   │   └── IsAdmin.php                         # Protezione route admin
│   │   ├── Requests/                               # Validazione input API
│   │   └── Resources/
│   │       └── UserResource.php                    # Serializzazione utente
│   ├── Mail/
│   │   └── SendOtpMail.php                         # Email con codice OTP
│   ├── Models/
│   │   ├── CarModel.php
│   │   ├── CarModelImage.php
│   │   ├── Category.php
│   │   ├── Color.php
│   │   ├── Configuration.php
│   │   ├── Engine.php
│   │   ├── Optional.php
│   │   └── User.php
│   └── Providers/
│       └── AppServiceProvider.php
├── bootstrap/
│   └── app.php                                     # Bootstrap dell'applicazione
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── cors.php                                    # Configurazione CORS
│   ├── database.php                                # Configurazione database
│   └── sanctum.php                                 # Autenticazione API token
├── database/
│   ├── factories/
│   │   └── UserFactory.php
│   └── migrations/                                 # Schema tabelle e relazioni
├── public/
│   └── index.php                                   # Entry point
├── resources/
│   └── views/
│       └── receipts/
│           └── configuration.blade.php             # Template PDF preventivo
├── routes/
│   ├── api.php                                     # Definizione delle rotte API
│   └── console.php
├── storage/
│   └── app/public/car-models/                      # Immagini modelli auto
├── tests/
    ├── Feature/
    └── Unit/

```