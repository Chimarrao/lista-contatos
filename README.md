# 📋 Lista de Contatos - Teste Técnico

<p align="center">
  <a href="https://www.php.net/" target="_blank"><img src="https://img.shields.io/badge/PHP-8.3.12-blue.svg" alt="PHP Version"></a>
  <a href="https://laravel.com/" target="_blank"><img src="https://img.shields.io/badge/Laravel-11.43.2-red.svg" alt="Laravel Version"></a>
  <a href="https://phpunit.de/" target="_blank"><img src="https://img.shields.io/badge/PHPUnit-^10-green.svg" alt="PHPUnit"></a>
  <a href="https://jwt-auth.readthedocs.io/en/develop/" target="_blank"><img src="https://img.shields.io/badge/JWT-Auth-yellow.svg" alt="JWT Authentication"></a>
</p>

---

## 📌 Sobre o Projeto

Este repositório contém a implementação de uma **API RESTful** para gerenciamento de contatos, desenvolvida como parte do **Teste Técnico para Pessoa Desenvolvedora FullStack | PHP**.

O objetivo do projeto é demonstrar habilidades em:
- Desenvolvimento de APIs com Laravel.
- Validação robusta (ex.: CPF único por usuário e validação oficial de CPF).
- Integração com APIs externas (ViaCEP e Google Maps Geocoding).
- Autenticação JWT.
- Testes unitários automatizados.

---

## 🛠️ Tecnologias Utilizadas

- **PHP**: Versão 8.3.12  
- **Laravel**: Versão 11.43.2  
- **Banco de Dados**: SQLite (para facilitar a execução local).  
- **Autenticação**: JWT (via pacote [tymon/jwt-auth](https://jwt-auth.readthedocs.io/en/develop/)).  
- **Testes**: PHPUnit (32 testes unitários implementados).  

---

## 🚀 Funcionalidades Principais

1. **Cadastro de Contatos**  
   - Validação de CPF (algoritmo oficial).  
   - CPF único por usuário.  
   - Campos obrigatórios: nome, CPF, telefone, CEP, rua, número, bairro, cidade, estado, latitude e longitude.  
   - Campo complemento opcional.  

2. **Listagem de Contatos**  
   - Filtragem por nome ou CPF.  
   - Ordenação personalizada (padrão: ordem alfabética crescente por nome).  
   - Paginação controlada pelo frontend.  

3. **Visualização, Atualização e Exclusão de Contatos**  
   - Operações CRUD completas.  

---
### Pré-requisitos
- PHP 8.3.12 instalado.
- Composer instalado.
- Git instalado.

### Passos para Execução
1. **Clone o repositório**:
   ```bash
   git clone https://github.com/Chimarrao/lista-contatos
   cd lista-contatos
    ```
2. **Instale as dependências**:
    ```bash
    composer install
    ```
3. **Execute as migrações do banco de dados**:
    ```bash
    php artisan migrate
    ```

---

### 🔧 Testes Unitários
Foram implementados **32 testes unitários** para garantir a cobertura das funcionalidades principais. Para executar os testes:

```bash
php artisan test
```

---

### 📝 Licença
Este projeto está licenciado sob a **MIT License**. Consulte o arquivo [LICENSE](LICENSE) para mais detalhes.