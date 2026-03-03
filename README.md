# CTLP

使用原生 PHP 开发的用户认证界面，及原生 HTML 编写的 Figma 拆分 UI 界面。

---

## 快速开始

### Task1-UI：原生 HTML 编写的 Figma 拆分 UI 界面

- **方式一（推荐）**：用浏览器直接打开  
  - 双击或拖拽打开 `task1-ui/index.html`  
  - 或在文件管理器中右键 `task1-ui/index.html` → 选择浏览器打开  

- **方式二**：使用本地 HTTP 服务（避免部分资源路径问题）  
  - 在项目根目录执行：`cd task1-ui && python -m http.server 8080`（需已安装 Python）  
  - 浏览器访问：`http://localhost:8080`

### Task2-Auth：原生 PHP 用户认证模块

*需 PHP 7.4+、Composer、MySQL 环境。

### 安装

1. **安装依赖**：在 `task2-auth` 目录执行 `composer install`
2. **环境配置**：将 `task2-auth/.env.example` 复制为 `task2-auth/.env`，填入数据库配置
3. **初始化数据库**：创建数据库后执行 `mysql -u root -p 数据库名 < task2-auth/database/schema.sql`
4. **启动与访问**：将 Web 站点根目录指向 `task2-auth/public/`（或配置虚拟主机 DocumentRoot 为 `task2-auth/public`），然后访问：
   - `http://你的域名或localhost/login.php` — 登录  
   - `http://你的域名或localhost/register.php` — 注册  
   - `http://你的域名或localhost/` — 首页（需登录）

#### 目录结构

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

#### 安全实现摘要


| 项        | 实现方式                                                                                                                                                                     |
| -------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **会话安全** | `public/bootstrap.php` 在任意输出前调用 `session_set_cookie_params()`：`httponly => true`、`secure`（HTTPS 时）、`samesite => Lax`，再 `session_start()`。所有入口统一 `require bootstrap.php`。 |
| **CSRF** | `Vechisss\Ctlp\Utils\Csrf`：生成/存储 Token，表单内 `Csrf::field()` 输出隐藏域，POST 时 `Csrf::validate()` 校验。登录、注册、找回密码、重置密码表单均已接入。                                                     |
| **密码**   | `password_hash(..., PASSWORD_DEFAULT)`（BCrypt）/ `password_verify()`，无明文存储。                                                                                               |
| **SQL**  | 全量 PDO 预处理，无拼接；`Connection` 中 `PDO::ATTR_EMULATE_PREPARES => false`。                                                                                                     |
| **输入**   | 邮箱 `filter_var(..., FILTER_VALIDATE_EMAIL)`，`Validator::cleanString()` 清理；密码强度校验。                                                                                        |
| **XSS**  | 页面与邮件模板中动态输出均使用 `htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`。                                                                                                            |


---

#### 主要流程

- **登录**：邮箱 + 密码 → `UserAuth::attemptLogin()` → 成功则 `SessionManager::login()` 并重定向。
- **注册**：填写邮箱与密码 → 发送 4 位验证码到邮箱 → 填写验证码完成注册 → 跳转登录。
- **找回密码**：输入邮箱 → 发送带 Token 的重置链接（限频）→ 点击链接打开重置页 → 设置新密码。

---

#### License

MIT