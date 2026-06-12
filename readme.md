# System Code Brazil Customer Identity Enhanced

## About Module

Extends Brazil Customer Identity with dedicated CPF, CNPJ, Social Name, and Trade Name attributes. Adds configurable visibility, validation, and optional sync to Magento native Tax/VAT and name fields for compatibility with core templates and integrations.

Requires [CustomerIdentityBrazil](https://github.com/m2-systemcode/CustomerIdentityBrazil).

### Configuration

**Stores > Configuration > System Code > Brazil Customer Identity**

Enhanced options appear under **Extra Fields Synchronization**, **Individual (CPF)**, and **Corporation (CNPJ)**.

### Screenshots

#### Admin Configuration
![Admin Configuration](.github/images/configuration.png)

#### Frontend
![Customer Registration](.github/images/frontend.png)

### Requirements

- `systemcode/customer-identity-brazil`

### How to install

#### ✓ Install by Composer (recommended)
```
composer require systemcode/customer-identity-brazil systemcode/customer-identity-brazil-enhanced
php bin/magento module:enable SystemCode_CustomerIdentityBrazilEnhanced
php bin/magento setup:upgrade
```

#### ✓ Install Manually
- Copy module to folder `app/code/SystemCode/CustomerIdentityBrazilEnhanced` and run commands:
```
php bin/magento module:enable SystemCode_CustomerIdentityBrazilEnhanced
php bin/magento setup:di:compile
php bin/magento setup:upgrade
```

### Suggested installation bundles

These modules are not required by Brazil Customer Identity Enhanced, but work well together for a complete Brazilian storefront.

#### Address essentials
Custom street labels, street prefix, and CEP autocomplete.
```
composer require systemcode/base systemcode/customer \
  systemcode/customer-street-lines \
  systemcode/customer-street-prefix \
  systemcode/customer-address-autocomplete-brazil
```

#### Address autocomplete + registration
Collect the customer address during account creation.
```
composer require systemcode/base systemcode/customer \
  systemcode/customer-address-autocomplete-brazil \
  systemcode/customer-address-registration
```

#### Address + Brazilian identity
Combine address autocomplete with CPF/CNPJ customer identity.
```
composer require systemcode/base systemcode/customer \
  systemcode/customer-address-autocomplete-brazil \
  systemcode/customer-identity-brazil
```

#### Full Brazilian storefront stack
Address, identity, registration, street labels, and street prefix.
```
composer require systemcode/base systemcode/customer \
  systemcode/customer-address-autocomplete-brazil \
  systemcode/customer-address-registration \
  systemcode/customer-street-lines \
  systemcode/customer-street-prefix \
  systemcode/customer-identity-brazil
```

#### Full stack with Enhanced identity
Dedicated CPF, CNPJ, Social Name, and Trade Name fields.
```
composer require systemcode/base systemcode/customer \
  systemcode/customer-address-autocomplete-brazil \
  systemcode/customer-address-registration \
  systemcode/customer-street-lines \
  systemcode/customer-street-prefix \
  systemcode/customer-identity-brazil \
  systemcode/customer-identity-brazil-enhanced
```

### License
OSL-3.0

### Authors
* [Eduardo Diogo Dias](https://github.com/eduardoddias)


---


## Sobre o Módulo

Estende a Identidade do Cliente (Brasil) com atributos dedicados de CPF, CNPJ, Razão Social e Nome Fantasia. Adiciona visibilidade configurável, validação e sincronização opcional com os campos nativos Tax/VAT e nome do Magento para compatibilidade com modelos e integrações do core.

Requer o [CustomerIdentityBrazil](https://github.com/m2-systemcode/CustomerIdentityBrazil).

### Configuração

**Lojas > Configuração > System Code > Brazil Customer Identity**

As opções Enhanced aparecem em **Sincronização de Campos Adicionais**, **Pessoa Física (CPF)** e **Pessoa Jurídica (CNPJ)**.

### Screenshots

#### Configuração no Admin
![Configuração no Admin](.github/images/configuration.png)

#### Frontend
![Customer Registration](.github/images/frontend.png)

### Requisitos

- `systemcode/customer-identity-brazil`

### Como Instalar

#### ✓ Instalação via Composer (recomendado)
```
composer require systemcode/customer-identity-brazil systemcode/customer-identity-brazil-enhanced
php bin/magento module:enable SystemCode_CustomerIdentityBrazilEnhanced
php bin/magento setup:upgrade
```

#### ✓ Instalação Manual
- Copie o módulo para `app/code/SystemCode/CustomerIdentityBrazilEnhanced` e execute:
```
php bin/magento module:enable SystemCode_CustomerIdentityBrazilEnhanced
php bin/magento setup:di:compile
php bin/magento setup:upgrade
```

### Combinações de instalação sugeridas

Estes módulos não são obrigatórios para o Brazil Customer Identity Enhanced, mas combinam bem para uma loja brasileira completa.

#### Essenciais de endereço
Rótulos de rua, prefixo de logradouro e autocomplete de CEP.
```
composer require systemcode/base systemcode/customer \
  systemcode/customer-street-lines \
  systemcode/customer-street-prefix \
  systemcode/customer-address-autocomplete-brazil
```

#### Autocomplete + cadastro
Coleta o endereço do cliente já no cadastro.
```
composer require systemcode/base systemcode/customer \
  systemcode/customer-address-autocomplete-brazil \
  systemcode/customer-address-registration
```

#### Endereço + identidade brasileira
Autocomplete de endereço com identidade CPF/CNPJ.
```
composer require systemcode/base systemcode/customer \
  systemcode/customer-address-autocomplete-brazil \
  systemcode/customer-identity-brazil
```

#### Stack completo brasileiro
Endereço, identidade, cadastro, rótulos de rua e prefixo de logradouro.
```
composer require systemcode/base systemcode/customer \
  systemcode/customer-address-autocomplete-brazil \
  systemcode/customer-address-registration \
  systemcode/customer-street-lines \
  systemcode/customer-street-prefix \
  systemcode/customer-identity-brazil
```

#### Stack completo com identidade Enhanced
Campos dedicados de CPF, CNPJ, Razão Social e Nome Fantasia.
```
composer require systemcode/base systemcode/customer \
  systemcode/customer-address-autocomplete-brazil \
  systemcode/customer-address-registration \
  systemcode/customer-street-lines \
  systemcode/customer-street-prefix \
  systemcode/customer-identity-brazil \
  systemcode/customer-identity-brazil-enhanced
```

### Licença
OSL-3.0

### Autores
* [Eduardo Diogo Dias](https://github.com/eduardoddias)
