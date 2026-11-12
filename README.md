# 留言板（Message Board）

原生 PHP + MySQL 实现的留言板，练习表单提交、Session 登录、数据库增删查与点赞。

## 功能

- 用户注册 / 登录 / 退出（密码 `password_hash` 加密）
- 发布留言（需登录）
- 留言列表（用户名、内容、时间、点赞数）
- 点赞 / 取消点赞（同一用户对同一条只能赞一次）
- 删除留言（只能删自己的）
- 提供一套 JSON API，供 Apifox / Vue 调试使用

## 环境要求

- phpStudy（Nginx 或 Apache + PHP 7.4+ + MySQL 5.7+）
- PHP 扩展：mysqli、mbstring
- 本机验证环境：PHP 8.0.30 + MySQL 5.7.26

## 快速开始

1. 把项目放到网站根目录：

   ```
   D:\phpstudy_pro\WWW\message\
   ```

2. 复制配置模板并填写数据库信息：

   ```bash
   cp db.example.php db.php
   ```

   编辑 `db.php`，改成你自己的账号密码。

3. 建库建表（在 phpMyAdmin 里执行 `sql/init.sql`，或手动执行下面 SQL）：

   ```sql
   CREATE DATABASE IF NOT EXISTS message_board
     DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
   USE message_board;

   CREATE TABLE users (
     id INT UNSIGNED NOT NULL AUTO_INCREMENT,
     username VARCHAR(50) NOT NULL,
     password VARCHAR(255) NOT NULL,
     created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
     PRIMARY KEY (id),
     UNIQUE KEY uk_users_username (username)
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

   CREATE TABLE message (
     id INT UNSIGNED NOT NULL AUTO_INCREMENT,
     user_id INT UNSIGNED NOT NULL,
     content TEXT NOT NULL,
     created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
     PRIMARY KEY (id),
     KEY idx_message_user (user_id),
     CONSTRAINT fk_message_user FOREIGN KEY (user_id) REFERENCES users (id)
       ON DELETE CASCADE ON UPDATE CASCADE
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

   CREATE TABLE likes (
     id INT UNSIGNED NOT NULL AUTO_INCREMENT,
     message_id INT UNSIGNED NOT NULL,
     user_id INT UNSIGNED NOT NULL,
     created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
     PRIMARY KEY (id),
     UNIQUE KEY uk_likes_msg_user (message_id, user_id),
     CONSTRAINT fk_likes_message FOREIGN KEY (message_id) REFERENCES message (id)
       ON DELETE CASCADE ON UPDATE CASCADE,
     CONSTRAINT fk_likes_user FOREIGN KEY (user_id) REFERENCES users (id)
       ON DELETE CASCADE ON UPDATE CASCADE
   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
   ```

4. 启动 phpStudy 的 Nginx（或 Apache）与 MySQL

5. 访问 <http://localhost/message/index.php>

## 页面路由

| 文件 | 说明 |
|---|---|
| index.php | 首页：留言列表 + 发布框 |
| login.php / register.php / logout.php | 登录 / 注册 / 退出 |
| save.php | 处理发布留言 |
| like.php | 处理点赞 / 取消 |
| delete.php | 处理删除留言 |

## JSON 接口（Apifox）

Base URL：`http://localhost/message/api`

| 方法 | 路径 | 说明 | 需要登录 |
|---|---|---|---|
| POST | /login.php | 登录 | 否 |
| POST | /register.php | 注册 | 否 |
| POST | /logout.php | 退出 | 是 |
| GET | /messages.php | 留言列表 | 否（登录后返回 liked 状态） |
| POST | /message_create.php | 发布留言 | 是 |
| POST | /message_delete.php | 删除留言 | 是 |
| POST | /like.php | 点赞 / 取消 | 是 |

统一返回格式：

```json
{"code": 0, "msg": "ok", "data": {}}
```

`code` 为 0 表示成功，非 0 为错误（错误码见 `api/_common.php`）。

## 目录结构

```
message/
├─ index.php            首页
├─ login.php            登录
├─ register.php         注册
├─ logout.php           退出
├─ save.php             发布留言
├─ like.php             点赞
├─ delete.php           删除
├─ auth.php             Session 与登录校验
├─ functions.php        公共函数
├─ db.php              数据库配置（已忽略，不提交）
├─ db.example.php      配置模板
├─ style.css           样式
└─ api/                JSON 接口
   ├─ _common.php
   ├─ login.php
   ├─ register.php
   ├─ logout.php
   ├─ messages.php
   ├─ message_create.php
   ├─ message_delete.php
   └─ like.php
```

## 安全说明

- 密码使用 `password_hash()` / `password_verify()`
- 所有数据库操作使用预处理语句，防 SQL 注入
- 页面输出统一 `htmlspecialchars()`，防 XSS
- 删除留言时校验 `user_id`，只能删自己的
- `db.php` 已加入 `.gitignore`，不会提交到仓库