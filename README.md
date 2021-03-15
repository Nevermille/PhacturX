# PhacturX

## Overview

A simple PHP library for Factur-X file creation

## Compatibility

This library has been tested for PHP 7.3 and higher

## Installation

Just use composer in your project:

```
composer require lianhua/phacturx
```

If you don't use composer, clone or download this repository, all you need is inside the src directory.

## Usage

Create a FacturX object with the PDF and XML files paths and call the createFacturX function with output PDF file path

```php
$fx = new \Lianhua\PhacturX\FacturX("in.pdf", "in.xml");
$fx->createFacturX("out.pdf");
```
