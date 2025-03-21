# Ovesio AI Module for OpenCart

## Overview
This module integrates OpenCart with [Ovesio.com](https://ovesio.com) to provide AI-powered translation capabilities for your e-commerce store.

## Features
- Compatible with OpenCart **2.3 - 3.x**
- Supports automatic and manual translations
- Customizable translation fields (Name, Description, Meta Title, Tags, etc.)
- Language association and mapping system
- Support for live translations
- Ability to exclude out-of-stock and disabled products from translations
- API-based authentication with an API Token

## Installation
### 1. Upload and Install the Module
1. Download the module archive.
2. Navigate to **Extensions > Installer** in your OpenCart admin panel.
3. Upload the module file and wait for installation to complete.
4. Go to **Extensions > Modifications** and click the **Refresh** button.
5. Navigate to **Extensions > Modules** and find `Ovesio AI Module`.
6. Click **Install** and then **Edit** to configure the module.

### 2. Configure the Module
1. **Enable the module** under the `General` tab.
2. **Set the API URL**: `https://ovesio.com/v1/`
3. **Enter your API Token** (provided by Ovesio.com).
4. **Set the catalog language** (e.g., `English`).
5. Under `Translate Settings`, configure the languages you want to translate and select the corresponding ISO2 codes.
6. Select which product and category fields should be translated.
7. Enable or disable live translations based on your preference.

### 3. Customization (Optional)
If your store requires custom modifications, you can edit:
```
system/ovesio.ocmod.xml_
```
After making changes, rename the file to:
```
system/ovesio.ocmod.xml
```
Then refresh modifications in OpenCart.

## Usage
- The module will automatically translate product names, descriptions, and metadata according to the settings configured.
- It can also provide translation feeds that can be accessed externally.
- Ensure the callback URL provided is accessible for live translation updates.

---

This module is developed and maintained by **Ovesio.com**.
