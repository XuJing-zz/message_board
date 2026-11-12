# 内容后台（文章 + 点赞 + 留言）

原生 PHP + MySQL 实现的内容管理系统：首页展示文章列表，点进文章可以点赞和留言（留言挂在文章下），后台可管理文章与留言。

## 环境要求

- phpStudy（Nginx 或 Apache + PHP 7.4+ + MySQL 5.7+）
- PHP 扩展：mysqli、mbstring
- 本机验证：PHP 8.0.30 + MySQL 5.7.26

## 快速开始

1. 项目放到 `D:\phpstudy_pro\WWW\message\`
2. 复制配置并填写数据库信息：

   ```bash
   cp db.example.php db.php
   ```

3. 导入数据库：

   ```bash
   mysql -u root -p < sql/init.sql
   ```

   或 phpMyAdmin → SQL → 粘贴 `sql/init.sql` 执行。

4. 启动 phpStudy 的 Nginx 与 MySQL
5. 前台：<http://localhost/message/index.php>
6. 后台：<http://localhost/message/admin/login.php>

## 测试账号

| 角色 | 账号 | 密码 | 入口 |
|---|---|---|---|
| 管理员 | admin | 123456 | `/admin/login.php` |
| 普通用户 | 自行注册 | — | `/register.php` |

## 页面结构

### 前台

| 文件 | 说明 |
|---|---|
| `index.php` | **首页：文章列表**（只显示已发布，分页） |
| `article_detail.php?id=` | 文章详情：正文 + 点赞 + **该文章的留言** |
| `article_like.php` | 点赞 / 取消（需登录） |
| `article_comment.php` | 发表留言（需登录） |
| `login.php` / `register.php` / `logout.php` | 登录 / 注册 / 退出 |

导航规则：普通用户看到「登录/注册」或「用户名 + 退出」；**管理员额外看到「管理后台」**。

### 后台

| 文件 | 说明 |
|---|---|
| `admin/login.php` | 管理员登录 |
| `admin/index.php` | 文章列表（分页、状态筛选） |
| `admin/edit.php` | 新增 / 编辑文章 |
| `admin/comments.php` | 留言列表与删除 |
| `admin/article_delete.php` | 删除文章（连带清理点赞与留言） |
| `admin/logout.php` | 退出 |

### 存档

`legacy/` 是第一阶段「独立留言板」的代码（`save.php` / `like.php` / `delete.php` / `functions.php`），仅供对照，前台不再进入。

## JSON 接口（Apifox）

Base URL：`http://localhost/message/api`

统一返回：

```json
{ "code": 0, "msg": "ok", "data": {} }
```

`code = 0` 成功，非 0 失败。

### 管理员

| 方法 | 路径 | 说明 |
|---|---|---|
| POST | `/admin/login.php` | 登录，body: `username`、`password` |
| POST | `/admin/logout.php` | 退出 |
| GET | `/admin/me.php` | 当前登录信息 |

### 文章（后台）

| 方法 | 路径 | 说明 |
|---|---|---|
| GET | `/admin/articles.php` | 列表，参数 `page`、`size`、`status`、`keyword` |
| GET | `/admin/article.php?id=1` | 详情 |
| POST | `/admin/article_save.php` | 新增/修改：`id`(改时传)、`title`、`cover`、`content`、`status` |
| POST | `/admin/article_delete.php` | 删除，body: `id` |

### 文章（用户端）

| 方法 | 路径 | 说明 |
|---|---|---|
| GET | `/articles.php` | 已发布文章列表，参数 `page`、`size` |
| GET | `/article.php?id=1` | 详情，含 `like_count`、`liked` |

### 点赞

| 方法 | 路径 | 说明 |
|---|---|---|
| POST | `/article_like.php` | 点赞，body: `article_id`，返回最新 `like_count` |
| POST | `/article_unlike.php` | 取消点赞 |

> 同一用户重复点赞返回 `code = 1004`。

### 留言

| 方法 | 路径 | 说明 |
|---|---|---|
| GET | `/comments.php?article_id=1` | 按文章查留言 |
| POST | `/comments.php` | 发表留言，body: `article_id`、`content` |
| GET | `/admin/comments.php` | 管理端留言列表，参数 `page`、`size`、`article_id` |
| POST | `/admin/comment_delete.php` | 删除留言，body: `id` |

## 目录结构

```
message/
├─ index.php                首页：文章列表
├─ article_detail.php       文章详情（点赞 + 留言）
├─ article_like.php         点赞处理
├─ article_comment.php      留言处理
├─ login.php / register.php / logout.php
├─ auth.php                 会话与登录校验
├─ db.php                   数据库配置（已忽略，不提交）
├─ db.example.php           配置模板
├─ style.css                样式
├─ legacy/                  第一阶段留言板（存档）
│  ├─ save.php / like.php / delete.php
│  └─ functions.php
├─ admin/                   HTML 管理后台
│  ├─ login.php
│  ├─ index.php
│  ├─ edit.php
│  ├─ comments.php
│  ├─ article_delete.php
│  └─ logout.php
├─ api/                     JSON 接口
│  ├─ _common.php
│  ├─ admin/                管理端接口
│  ├─ articles.php / article.php
│  ├─ article_like.php / article_unlike.php
│  ├─ comments.php
│  └─ （第一阶段留言板接口：login / register / messages / like ...）
└─ sql/
   └─ init.sql              建库建表脚本（含默认管理员）
```

## 数据表

| 表 | 说明 |
|---|---|
| `users` | 用户（`role`: admin / user） |
| `articles` | 文章（标题、封面、正文、状态） |
| `article_likes` | 文章点赞（唯一索引防重复） |
| `article_comments` | 文章留言 |
| `message` / `likes` | 第一阶段留言板遗留表（存档） |

## 安全说明

- 密码使用 `password_hash()` / `password_verify()`
- 全部数据库操作使用预处理语句
- 输出统一 `htmlspecialchars()`，防 XSS
- 管理端页面与接口校验 `role = admin`
- 删除文章时同步清理点赞与留言
- `db.php` 已加入 `.gitignore`，不会提交