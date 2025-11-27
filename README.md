# Klawio Website Audit System

A fast, lightweight and open-source Website Audit Tool combining:

Google Lighthouse Performance Score (via PageSpeed API)

Klawio 100-Point SEO Engine (custom scoring)

Mobile performance score

Key Findings engine

Automatic email reporting

Fully PHP backend (no Composer needed)

Simple embeddable frontend

Perfect for agencies, freelancers, and SaaS builders looking to offer free website audits as a lead generator.

🚀 Features
🧪 Performance Auditing

Google Lighthouse performance score

Mobile-mode scanning

Server latency info

Overall Klawio score calculation

🔍 Klawio SEO Engine (100 points)

Includes checks for:

title tag

Meta description

h1 existence

Canonical tag

OG title / description

Robots.txt

Sitemap.xml

Image ALT attributes

Keyword density (basic)

Noindex detection

📧 Email Report

Audit emailed to the user

Copy emailed to admin

HTML formatted report

🌐 Frontend

Simple HTML widget

JavaScript fetch handler

Minimal, clean UI

Easy to embed in WordPress / Elementor / Divi / any site

🖥 Backend

Pure PHP

No framework

No dependencies

Works on any shared hosting

Secure config file

Clean JSON API output

📁 Project Structure
klawio-website-audit-system/
│
├── api/
│   ├── api.php               (Main backend)
│   ├── sample.config.php     (Template for user credentials)
│
├── web/
│   ├── index.html            (Demo UI)
│   ├── audit.js              (Frontend logic)
│   ├── audit.css             (Optional styles)
│
└── README.md

🛠 Installation
1️⃣ Clone the repository
git clone https://github.com/mhakantatlici/klawio-website-audit-system

2️⃣ Create your config

Go to the api folder.

Copy:

sample.config.php → config.php


Fill in:

Your Google PageSpeed API Key

SMTP sender email

Your admin email

3️⃣ Upload to hosting

Any shared hosting or VPS works.
Requires only:

PHP 7.4+

cURL enabled

4️⃣ Test API

Go to:

yourdomain.com/api/api.php


Expected output:

{"error":"URL and email are required"}

🧪 Using the Audit UI

The frontend demo is inside /web.

You can embed index.html directly or copy the form + audit.js into any website (including WordPress).

🧩 API Endpoint

POST to:

/api/api.php

Body (x-www-form-urlencoded):
url=https://example.com
email=user@example.com

JSON Response:
{
  "status": "ok",
  "overall_score": 78,
  "performance_score": 66,
  "seo_score": 83,
  "mobile_score": 66,
  "findings": [...],
  "email_sent": true
}

📝 License

MIT License © 2025 Klawio Web Agency

This project is free to use, modify, and integrate into commercial SaaS products.
