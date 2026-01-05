# 🕒 Ponto Online - Sistema de Gestão e Quiosque de Ponto

![Badge Desenvolvido](http://img.shields.io/static/v1?label=STATUS&message=%20DESENVOLVIDO&color=GREEN&style=for-the-badge)
![HTML5](https://img.shields.io/badge/html5-%23E34F26.svg?style=for-the-badge&logo=html5&logoColor=white)
![CSS3](https://img.shields.io/badge/css3-%231572B6.svg?style=for-the-badge&logo=css3&logoColor=white)
![PHP](https://img.shields.io/badge/php-%23777BB4.svg?style=for-the-badge&logo=php&logoColor=white)
![JavaScript](https://img.shields.io/badge/javascript-%23323330.svg?style=for-the-badge&logo=javascript&logoColor=%23F7DF1E)
![Badge License](https://img.shields.io/badge/LICENSE-PROPRIETARY-red?style=for-the-badge)

Um sistema completo de controle de jornada de trabalho (Time Tracking), focado em segurança, auditoria e facilidade de uso. O projeto combina um **Quiosque de Ponto** para funcionários (Front-end) com um **Painel Administrativo** robusto (Back-end) para gestão de RH.

## 🚀 Funcionalidades

### 🏢 Para o Funcionário (Quiosque)
- **Registro Rápido:** Login simplificado via CPF e Senha para registrar Entrada/Saída.
- **Feedback Visual:** Mensagens claras de confirmação com data e hora.
- **Proteção de Dados:** Senhas criptografadas e máscaras de input.
- **Recuperação de Senha 2FA:** Fluxo inovador de solicitação via sistema + token de validação via WhatsApp.

### 🛡️ Para o Administrador (Dashboard)
- **Visão Geral:** Cards com métricas em tempo real (Funcionários, Turnos Ativos, Registros do Dia).
- **Gestão de Usuários:** Adicionar, editar status (Férias, Desligado), alterar permissões e excluir.
- **Auditoria de Ponto:**
  - Visualização de turnos em andamento.
  - "Lixeira" inteligente: Pontos cancelados não são apagados, mas movidos para uma área de auditoria com registro de saída forçada.
- **Console SQL:** Terminal integrado para manutenção avançada do banco de dados.
- **Segurança:** Proteção contra reenvio de formulário (PRG Pattern) e Sessões Segregadas.

---

## 🛠️ Tecnologias Utilizadas

- **Linguagem:** PHP (7.4 ou 8+)
- **Banco de Dados:** SQLite3 (Arquivo local, sem necessidade de servidor MySQL)
- **Front-end:** HTML5, CSS3 (Glassmorphism & Dashboard UI), JavaScript Vanilla.
- **Bibliotecas:** 
  - [FullCalendar](https://fullcalendar.io/) (Visualização de relatórios e lixeira).

---

## ⚙️ Instalação e Configuração

1. **Requisitos:**
   - Servidor Web (Apache/Nginx/IIS) ou PHP Built-in Server.
   - Extensão `sqlite3` habilitada no `php.ini`.

2. **Instalação:**
   - Clone este repositório ou extraia os arquivos na pasta pública do servidor (`www` ou `htdocs`).
   - Garanta que a pasta `./DB` tenha permissão de **leitura e escrita** (chmod 775 ou 777), pois o SQLite precisa criar arquivos temporários de bloqueio.

3. **Banco de Dados:**
   - O sistema utiliza o arquivo `./DB/db_pontos.db`.
   - Caso precise resetar, apague este arquivo. O sistema não recria a estrutura automaticamente, é necessário rodar o script de criação inicial (schema).

---

## 🔐 Níveis de Acesso

O sistema possui controle de acesso baseado na coluna `admin` da tabela `usuarios`:

| Nível | Função | Permissões |
| :--- | :--- | :--- |
| **0** | **Usuário** | Apenas bater ponto no Quiosque (`index.php`). Acesso negado ao painel. |
| **1** | **Admin** | Acesso total: Dashboard, Lixeira, Console SQL e Gestão de Usuários. |
| **2** | **Moderador** | Acesso restrito à Lixeira (Auditoria de pontos excluídos). |

---

## 📂 Estrutura de Pastas
/
├── index.php # Quiosque de Ponto (Login Funcionário)
├── login.php # Login Administrativo
├── cadastro.php # Registro de novos funcionários
├── adm.php # Dashboard Principal (Gestão)
├── lixeira.php # Auditoria de pontos cancelados
├── relatorios.php # Consulta de horas e espelho de ponto
├── recuperar.php # Fluxo de "Esqueci a Senha"
├── trocarsenha.php # Redefinição via Token
│
├── back*.php # Controladores PHP (Lógica de Backend)
├── logout.php # Encerramento de sessão
│
├── DB/
│ └── db_pontos.db # Banco de Dados SQLite
│
└── styles/ # Folhas de estilo CSS
├── stylepontos.css
├── styleadm.css
├── stylecadastro.css
├── stylelogin.css
└── stylerecupera.css

---

## 🧠 Fluxo de Recuperação de Senha (Token)

Para evitar custos com servidores de e-mail e garantir segurança em ambiente local:

1. O usuário solicita o reset em `recuperar.php`. O status muda para **"Solicitando"**.
2. O Admin vê o alerta no Dashboard e clica em **"🔑 Gerar Token"**.
3. O sistema gera um código de 6 dígitos (válido por 30min).
4. O Admin envia o código para o funcionário (via WhatsApp/Presencial).
5. O funcionário insere o token em `trocarsenha.php` e cria uma nova senha.

---

## 🎨 Design System

O projeto utiliza dois padrões visuais distintos para evitar confusão:
1.  **Quiosque/Login (Público):** Estilo Glassmorphism, gradientes e foco central.
2.  **Painel (Privado):** Estilo Dashboard, fundo claro, tabelas organizadas e Cards de informação.

---

## 📬 Contato

Carlos Eduardo Santos Ribeiro

LinkedIn: @crbr-dev

GitHub: IAmCarlosRibeiro

Email: crbrdev@gmail.com

---

## ⚖️ Licença

Este projeto é protegido por direitos autorais.
**Você pode:** Baixar e utilizar o aplicativo para uso pessoal.
**Você NÃO pode:** Modificar o código, distribuir cópias ou usar para fins comerciais sem permissão explícita do autor.

Consulte o arquivo `LICENSE` para mais detalhes.

---

Desenvolvido com 💙 por Carlos Ribeiro.
