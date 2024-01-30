# admetSAR Service

[![Latest Version on Packagist](https://img.shields.io/packagist/v/nekhbet/admetsar-service.svg?style=flat-square)](https://packagist.org/packages/nekhbet/admetsar-service)
[![Total Downloads](https://img.shields.io/packagist/dt/nekhbet/admetsar-service.svg?style=flat-square)](https://packagist.org/packages/nekhbet/admetsar-service)

Lets you retrieve ADMET properties for a chemical compound (defined by its SMILES code) via admetSAR v2 Service (http://lmmd.ecust.edu.cn/admetsar2/).

## Story of creating this package (with comments, full content)

Part 1: https://youtu.be/L-L7P9YcRf8

Part 2: https://youtu.be/RRhEbgwFIlg



## Installation

You can install the package via composer:

```bash
composer require nekhbet/admetsar-service
```

## Usage (also see examples/simple.php)

```php
$api = new admetSAR();
$id_job = $api
    ->setSMILESCode('Cc1cc(O)c2C(=O)c3c(O)cc(O)c4c3c3c2c1c1c2c3c3c4c(O)cc(O)c3C(=O)c2c(O)cc1C')
    ->submitJob();
print_r($api->parseJobResults($id_job));
```

```txt
Output example: 
...
Array
(
    [status] => parsed
    [data] => Array
        (
            [predictions] => Array
                (
                    [0] => Array
                        (
                            [probability] => 0.9808
                            [property] => Human Intestinal Absorption
                            [value] => +
                        )
                    [1] => Array
                        (
                            [probability] => 0.5675
                            [property] => Caco-2
                            [value] => -
                        )
                    [2] => Array
                        (
                            [probability] => 0.7000
                            [property] => Blood Brain Barrier
                            [value] => -
                        )
                        ...
                )
            [properties] => Array
                (
                    [0] => Array
                        (
                            [property] => Molecular Weight
                            [value] => 504.45
                        )
                    [1] => Array
                        (
                            [property] => AlogP
                            [value] => 5.08
                        )
                    [2] => Array
                        (
                            [property] => H-Bond Acceptor
                            [value] => 8
                        )
                    [3] => Array
                        (
                            [property] => H-Bond Donor
                            [value] => 6
                        )
                    [4] => Array
                        (
                            [property] => Rotatable Bonds
                            [value] => 0
                        )
                    [5] => Array
                        (
                            [property] => Applicability Domain
                            [value] => Array
                                (
                                    [class] => warning
                                    [content] => Warning
                                )

                        )
                )
            [regressions] => Array
                (
                    [0] => Array
                        (
                            [property] => Water solubility
                            [unit] => logS
                            [value] => -3.22
                        )
                    [1] => Array
                        (
                            [property] => Plasma protein binding
                            [unit] => 100%
                            [value] => 0.872
                        )
                )
        )
)

...
```

## Credits

-   [Sorin Trimbitas](https://github.com/nekhbet)

