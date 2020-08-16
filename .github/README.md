![Nebula readme icon](https://raw.githubusercontent.com/chrisblakley/Nebula/master/.github/assets/nebula-orb.png "Nebula readme icon")

---

**Nebula is a WordPress theme framework that focuses on enhancing development.**

The core features of Nebula make it a powerful tool for designing, developing, and analyzing WordPress websites consistently, but its deliberately uncomplicated code syntax also serves as a learning resource for programmers themselves.

![GitHub release (latest SemVer)](https://img.shields.io/github/v/release/chrisblakley/Nebula)
![GitHub commits since latest release (by SemVer)](https://img.shields.io/github/commits-since/chrisblakley/Nebula/latest)
![GitHub last commit](https://img.shields.io/github/last-commit/chrisblakley/Nebula)
![CodeQL](https://github.com/chrisblakley/Nebula/workflows/CodeQL/badge.svg?branch=master)
[![Codacy Badge](https://api.codacy.com/project/badge/Grade/1eb4554216644f5c9227df34343a9ae9)](https://www.codacy.com/app/greatblakes/Nebula?utm_source=github.com&amp;utm_medium=referral&amp;utm_content=chrisblakley/Nebula&amp;utm_campaign=Badge_Grade)
[![CodeFactor](https://www.codefactor.io/repository/github/chrisblakley/nebula/badge)](https://www.codefactor.io/repository/github/chrisblakley/nebula)

![Nebula screenshot](https://raw.githubusercontent.com/chrisblakley/Nebula/master/.github/assets/nebula-pwa.jpg "Nebula screenshot")

## Table of Contents
+ [Getting Started](#getting_started)
+ [Requirements & Recommendations](#requirements)
+ [Prerequisites](#prerequisites)
+ [Installation & Setup](#installation)
+ [Documentation](#documentation)
+ [Built Using](#built_using)

## Getting Started <a name="getting_started"></a>
These instructions will get Nebula setup to its recommended baseline.

### Requirements & Recommendations <a name="requirements"></a>
Nebula follows [WordPress recommended requirements](https://wordpress.org/about/requirements/), and **does not require any additional server software**. Browsers with >1% market share are officially supported.

To take full advantage of Nebula's features, the following are recommended:
+ WordPress Latest
+ PHP 7.0+
+ MySQL 5.6+
+ SSL

### Prerequisites <a name="prerequisites"></a>
[Download and install WordPress.](https://wordpress.org/)

### Installation & Setup <a name="installation"></a>

![Nebula installation](https://raw.githubusercontent.com/chrisblakley/Nebula/master/.github/assets/nebula-install.gif "Nebula installation")

1. [Download the .zip file of the Nebula theme.](https://github.com/chrisblakley/Nebula/archive/master.zip) Upload to the WordPress `/wp-content/themes/` directory.
2. Activate the Nebula theme and run the automated initialization.
3. This will automatically install and activate the Nebula Child theme.
4. It is recommended to rename the child theme (in `/assets/scss/style.scss` and the directory name itself).
5. Create a `/assets/img/meta/` directory in the child theme (or copy it from the Nebula parent).
6. Install and activate recommended plugins including the [Nebula Companion plugin](https://github.com/chrisblakley/Nebula-Companion).
7. Customize Nebula Options (including enabling Sass if desired)

## Documentation <a name="documentation"></a>
Comprehensive documentation is available at [https://nebula.gearside.com/](https://nebula.gearside.com/?utm_campaign=documentation&utm_medium=readme&utm_source=github&utm_content=full+documentation) along with a [testing checklist]((https://nebula.gearside.com/get-started/?utm_campaign=documentation&utm_medium=readme&utm_source=github&utm_content=testing+checklist)) and [launch guide](https://nebula.gearside.com/get-started/?utm_campaign=documentation&utm_medium=readme&utm_source=github&utm_content=launch+checklist).

## Performance
Performance data is updated automatically every hour. Your performance may vary depending on your hosting, plugins, and many other variables. This graphic is to simply illustrate Nebula's performance capabilities.
<br/><br/><img src="https://nebula.gearside.com/speedtest.svg" width="600px">

## Built Using <a name="built_using"></a>
+ [Bootstrap](https://github.com/twbs/bootstrap)
+ [Plugin Update Checker](https://github.com/YahnisElsts/plugin-update-checker)
+ [TGM Plugin Activation](https://github.com/TGMPA/TGM-Plugin-Activation)
+ [SCSSPHP](https://github.com/scssphp/scssphp)
+ [Device Detector](https://github.com/matomo-org/device-detector)
+ [Mmenu](https://github.com/FrDH/mmenu-js)
+ [Workbox](https://github.com/GoogleChrome/workbox)
+ [Autotrack](https://github.com/googleanalytics/autotrack)