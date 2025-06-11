# MENA Freight Hub

## Descrição do Projeto

O MENA Freight Hub é uma plataforma web B2B desenvolvida para empresas multinacionais da região Oriente Médio & Norte da África (MENA) que precisam gerenciar fretamento de veículos para transporte de mercadorias.

## Características Principais

- **Aplicação Multilíngue**: Suporte nativo para Inglês, Árabe e Francês (sem uso de tradutores automáticos)
- **Mercado Corporativo**: Voltado especificamente para empresas B2B
- **Gestão Completa**: Cadastro de empresas, veículos e geração de orçamentos
- **Interface Responsiva**: Design adaptável para desktop e mobile

## Funcionalidades

### 1. Landing Page Institucional
- Visão geral do serviço
- Apresentação dos benefícios
- Acesso ao portal do cliente

### 2. Portal do Cliente
- Sistema de login/logout
- Seleção de idioma (EN, AR, FR)
- Dashboard com estatísticas

### 3. Gestão de Empresas
- Cadastro de empresas clientes
- Múltiplos endereços por empresa
- Informações de contato

### 4. Gestão de Veículos
- Cadastro de diferentes tipos de veículos
- Controle de capacidade e identificação
- Associação com empresas

### 5. Sistema de Orçamentos
- Solicitação de orçamentos
- Cálculo automático baseado em tipo de veículo e período
- Histórico de orçamentos

## Tecnologias Utilizadas

- **Frontend**: HTML5, CSS3, JavaScript
- **Backend**: PHP 8.1
- **Banco de Dados**: MySQL 8.0
- **Servidor Web**: Apache/PHP Built-in Server

## Estrutura do Banco de Dados

```sql
-- Principais tabelas
EMPRESA (id, nome, cnpj, email, telefone)
ENDERECO (id, empresa_id, pais, cidade, rua)
VEICULO (id, tipo, capacidade, placa, empresa_id)
USUARIO (id, empresa_id, nome, email, senha_hash)
ORCAMENTO (id, empresa_id, veiculo_id, origem, destino, data_inicio, data_fim, valor)
```

## Instalação e Configuração

### Pré-requisitos
- PHP 8.1 ou superior
- MySQL 8.0 ou superior
- Servidor web (Apache/Nginx) ou PHP built-in server

### Passos de Instalação

1. **Clone o repositório**
   ```bash
   git clone [URL_DO_REPOSITORIO]
   cd mena-freight-hub
   ```

2. **Configure o banco de dados**
   ```bash
   mysql -u root -p < database.sql
   ```

3. **Configure a conexão**
   - Edite o arquivo `config.php` com suas credenciais de banco

4. **Inicie o servidor**
   ```bash
   php -S localhost:8000
   ```

5. **Acesse a aplicação**
   - Navegue para `http://localhost:8000`

## Credenciais de Teste

- **Email**: test@example.com
- **Senha**: password

## Estrutura de Arquivos

```
/mena-freight-hub
├── index.html              # Landing page
├── login.html              # Página de login
├── dashboard.php           # Dashboard principal
├── dashboard_demo.php      # Dashboard de demonstração
├── config.php              # Configuração do banco
├── config_demo.php         # Configuração de demonstração
├── logout.php              # Script de logout
├── database.sql            # Script de criação do banco
├── /clientes/              # CRUD de empresas
├── /veiculos/              # CRUD de veículos
├── /orcamentos/            # CRUD de orçamentos
├── /includes/              # Arquivos auxiliares
├── /assets/                # CSS, JS, Imagens
├── /lang/                  # Arquivos de idioma
├── /scripts/               # JavaScript auxiliar
└── README.md               # Este arquivo
```

## Suporte Multilíngue

O sistema implementa suporte multilíngue através de arquivos PHP que retornam arrays associativos:

- `lang/en.php` - Inglês
- `lang/ar.php` - Árabe  
- `lang/fr.php` - Francês

A troca de idioma é feita via JavaScript e cookies, sem necessidade de tradutores automáticos.

## Demonstração

Uma versão de demonstração está disponível em `dashboard_demo.php` que utiliza dados simulados em memória, permitindo testar todas as funcionalidades sem necessidade de configurar o banco de dados.

## Contribuição

Este projeto foi desenvolvido como parte do Hackathon Senac RIP. Para contribuições:

1. Faça um fork do projeto
2. Crie uma branch para sua feature
3. Commit suas mudanças
4. Push para a branch
5. Abra um Pull Request

## Licença

Este projeto está sob licença MIT. Veja o arquivo LICENSE para mais detalhes.

## Contato

Para mais informações sobre o projeto, entre em contato através do repositório GitHub.

---

**Desenvolvido para o Hackathon Senac RIP 2025**

