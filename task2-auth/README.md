# Task2-Auth：原生 PHP 用户认证模块

基于**纯原生 PHP** 的高性能、高安全性用户认证系统，零框架依赖，逻辑与表现层分离。

---

## 项目概览

- **零框架依赖**：认证与业务逻辑在 `src/` 中纯 PHP 实现，仅通过 Composer 引入 PHPMailer、vlucas/phpdotenv 等库。
- **防御性编程**：密码哈希、PDO 预处理、输入校验、限频、CSRF/XSS 防护等作为第一优先级。
- **模块化设计**：入口在 `/public`，核心在 `/src`，模板在 `/templates`，配置通过 `.env` 集中管理。

---

## 技术栈

- PHP 7.4+（推荐 8.x）
- PDO + MySQL / MariaDB（utf8mb4）
- Composer：`phpmailer/phpmailer`、`vlucas/phpdotenv`

---

## 目录结构

```
task2-auth/
├── public/                 # 入口与静态资源
│   ├── bootstrap.php       # 统一引导：安全会话 + 自动加载（所有入口必须 require）
│   ├── index.php           # 欢迎页（需登录）
│   ├── login.php           # 登录
│   ├── register.php        # 注册（邮箱验证码）
│   ├── forgot-password.php # 找回密码
│   ├── reset-password.php  # 重置密码（邮件链接）
│   ├── logout.php          # 退出
│   └── assets/             # CSS / JS / 图片
├── src/                    # 核心逻辑
│   ├── Auth/
│   │   ├── UserAuth.php    # 登录 / 注册 / 找回与重置密码
│   │   └── SessionManager.php
│   ├── Database/
│   │   └── Connection.php  # PDO 单例，从 .env 读配置
│   └── Utils/
│       ├── Validator.php   # 邮箱 / 密码校验与字符串清理
│       ├── Csrf.php        # CSRF Token 生成与校验
│       └── Mailer.php      # 邮件发送（验证码、重置链接）
├── templates/              # 表现层
│   ├── welcome.html
│   └── email/              # 邮件 HTML 模板
├── database/
│   └── schema.sql          # 用户表结构
├── .env                    # 本地环境配置（由 .env.example 复制而来，已加入 .gitignore，勿提交）
├── .env.example            # 环境变量模板（可提交），复制为 .env 后填写
├── composer.json
└── README.md
```

---

## 快速开始

### 1. 安装依赖

```bash
composer install
```

### 2. 环境配置

**程序只读取 `task2-auth/.env` 文件，不会读 `.env.example`。** 克隆项目后请：

1. 在 `task2-auth/` 下将 `.env.example` 复制为 `.env`：
   ```bash
   cd task2-auth
   cp .env.example .env   # Linux/macOS
   # 或 copy .env.example .env   # Windows CMD
   ```
2. 编辑 `.env`，填入真实的数据库与 SMTP 等信息。

不要直接在 `.env.example` 里填写真实密码并提交——`.env.example` 会被提交到仓库，仅作模板；`.env` 已被 `.gitignore` 忽略，用于本地敏感配置。

### 3. 数据库

创建数据库后执行：

```bash
mysql -u root -p ctlp < database/schema.sql
```

### 4. Web 入口

将站点根目录指向 `public/`（或配置虚拟主机 DocumentRoot 为 `public`），访问：

- `http://localhost/login.php` — 登录
- `http://localhost/register.php` — 注册
- `http://localhost/` — 首页（需登录）

---

## 安全实现摘要


| 项        | 实现方式                                                                                                                                                                     |
| -------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **会话安全** | `public/bootstrap.php` 在任意输出前调用 `session_set_cookie_params()`：`httponly => true`、`secure`（HTTPS 时）、`samesite => Lax`，再 `session_start()`。所有入口统一 `require bootstrap.php`。 |
| **CSRF** | `Vechisss\Ctlp\Utils\Csrf`：生成/存储 Token，表单内 `Csrf::field()` 输出隐藏域，POST 时 `Csrf::validate()` 校验。登录、注册、找回密码、重置密码表单均已接入。                                                     |
| **密码**   | `password_hash(..., PASSWORD_DEFAULT)`（BCrypt）/ `password_verify()`，无明文存储。                                                                                               |
| **SQL**  | 全量 PDO 预处理，无拼接；`Connection` 中 `PDO::ATTR_EMULATE_PREPARES => false`。                                                                                                     |
| **输入**   | 邮箱 `filter_var(..., FILTER_VALIDATE_EMAIL)`，`Validator::cleanString()` 清理；密码强度校验。                                                                                        |
| **XSS**  | 页面与邮件模板中动态输出均使用 `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`。                                                                                                            |


---

## 主要流程

- **登录**：邮箱 + 密码 → `UserAuth::attemptLogin()` → 成功则 `SessionManager::login()` 并重定向。
- **注册**：填写邮箱与密码 → 发送 4 位验证码到邮箱 → 填写验证码完成注册 → 跳转登录。
- **找回密码**：输入邮箱 → 发送带 Token 的重置链接（限频）→ 点击链接打开重置页 → 设置新密码。

---

## License

MIT