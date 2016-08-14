# packages-scanner

This application compares packages from your own packages.json repository with information from Packagist.

## Installation

### Installation as own application 

```bash
$ composer create-project ichhabrecht/packages-scanner
```

### Installation as requirement 

```bash
$ composer require ichhabrecht/packages-scanner
```

## Usage

To get an overview of existing commands you can simply list them with

```bash
$ vendor/bin/packages-scanner list
```

### package:validate

The command validates the package names found in the provided packages.json
and lists those which cannot be registered on Packagist.

```bash
$ vendor/bin/packages-scanner package:validate https://example.com
```

### package:register

The command lists all packages found in your packages.json which are not registered on Packagist yet.
Furthermore it shows the url and author information of each package.

```bash
$ vendor/bin/packages-scanner package:register https://example.com
```

**Options**

*--exclude-vendor*

Comma separated list of vendor names to exclude from Packagist check.

### vendor:list

The command lists all vendor names of the packages found in the provided packages.json.
It shows the registration status of the vendor name on Packagist.

```bash
$ vendor/bin/packages-scanner vendor:list https://example.com
```

**Options**

*--only-registered*

Shows only vendor names that are registered on Packagist already.

*--only-unregistered*

Shows only vendor names that are not registered on Packagist yet.

### vendor:register

The command lists all vendor names with their packages found in your packages.json which are not registered on Packagist yet.
Furthermore it shows the url and author information of each package.

```bash
$ vendor/bin/packages-scanner vendor:register https://example.com
```
