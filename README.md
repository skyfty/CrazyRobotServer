# 赛博炮手后端服务

赛博炮手后端项目，基于 FastAdmin（ThinkPHP + Bootstrap）构建，提供游戏业务 API、后台管理、用户体系、装备/技能/道具等数据管理，以及文件和 Excel 上传/导入能力。

## 项目与环境信息

| 项目 | 信息 |
| --- | --- |
| 后端管理站点 | [https://gsczjl.zlgccn.com](https://gsczjl.zlgccn.com) |
| Excel 上传站点 | [https://wps.gsczjl.zlgccn.com](https://wps.gsczjl.zlgccn.com) |
| 数据库名 | `defend` |
| 数据库用户名 | `defend` |
| 数据库密码 | `defend` |
| 后端项目 Git 地址 | `git@github.com:skyfty/CrazyRobotServer.git` |
| 服务器部署目录 | `/www/wwwroot/CrazyRobotAdmin` |

> 以上数据库信息属于部署凭据，仅限授权的运维和开发人员使用。生产环境建议通过 `.env` 或服务器环境变量配置，并定期更换密码；不要将新的生产密码提交到 Git。

## 技术栈

- PHP `>= 7.4`
- ThinkPHP（FastAdmin 使用的框架）
- FastAdmin `1.6.1`
- MySQL，默认字符集为 `utf8mb4`
- Bootstrap、jQuery、RequireJS 等前端依赖
- PhpSpreadsheet：支持 `xls`、`xlsx`、`csv` 文件读取/导入
- Composer：PHP 依赖管理
- npm + Grunt：前端资源构建

## 目录结构

```text
CrazyRobotServer/
├── addons/                    # FastAdmin 插件及扩展
├── application/               # 业务代码（ThinkPHP 多模块）
│   ├── admin/                 # 后台管理模块：控制器、模型、视图、权限、后台上传
│   ├── api/                   # 对外 API：用户、认证、装备、技能、道具、关卡等
│   ├── common/                # 公共控制器、模型、服务、上传、Token、鉴权等
│   ├── index/                 # 前台页面、用户页面及前台 Ajax 上传
│   ├── config.php             # 应用总配置
│   ├── database.php           # 数据库配置（支持环境变量覆盖）
│   ├── route.php              # 路由配置
│   └── extra/                 # 上传、站点、队列等扩展配置
├── extend/fast/               # FastAdmin 自定义扩展类，如 Auth、Tree、Rsa、Http
├── public/                    # Web 根目录及静态资源
│   ├── index.php              # 默认应用入口
│   ├── JKZSjbdETB.php         # 后台隐藏入口文件，绑定 admin 模块
│   ├── assets/                # CSS、LESS、JavaScript、第三方前端资源
│   ├── uploads/               # 本地上传文件目录
│   └── template/              # 上传/导入相关模板目录
├── runtime/                   # ThinkPHP 运行时缓存、日志（不建议提交）
├── composer.json              # PHP 依赖及运行要求
├── package.json               # 前端依赖及构建脚本
├── Gruntfile.js               # 前端资源构建配置
└── .env.sample                # 环境变量配置示例
```

## 代码说明

### API 模块：`application/api`

API 通过 `public/index.php` 进入，使用 ThinkPHP 控制器处理请求，主要模块如下：

| 文件/目录 | 作用 |
| --- | --- |
| `controller/Auth.php` | 登录态、注册、用户信息、密码、Token、装备数据同步等认证相关功能 |
| `controller/User.php` | 用户注册、登录、微信/手机登录、个人资料及账号操作 |
| `controller/Equipment.php` | 装备查询、装备穿戴、升级和玩家装备数据 |
| `controller/Prop.php` | 背包、道具获得/消耗、签到、关卡进度及属性升级 |
| `controller/Skill.php` | 技能查询、获得、升级和当前技能设置 |
| `controller/Buff.php` | Buff 查询及 Buff 事件处理 |
| `controller/Gunner.php` | 关卡查询及奖励处理 |
| `controller/Random.php` | 随机研究、随机装备等逻辑 |
| `controller/Common.php` | API 初始化、上传和验证码 |
| `controller/Validate.php` | 用户名、邮箱、手机号及验证码校验 |
| `model/` | 游戏实体模型，如装备、技能、敌人、关卡、波次、Buff、升级等 |

### 后台模块：`application/admin`

后台绑定到 `admin` 模块，由 `public/JKZSjbdETB.php` 作为入口。该模块继承 FastAdmin 的后台能力，包含：

- 管理员、角色组、权限规则和后台操作日志
- 用户、分类、系统配置、附件管理
- 后台首页、仪表盘及通用 Ajax
- 通用 CRUD、API 文档/代码生成等 FastAdmin 工具
- Excel/CSV 导入入口和后台文件上传

后台权限规则主要由 `application/admin/model/AuthRule.php`、`AuthGroup.php`、`AuthGroupAccess.php` 等模型及对应控制器维护。

### 公共与前台模块

- `application/common/controller/Api.php`、`Backend.php`、`Frontend.php`：API、后台、前台的公共基类。
- `application/common/library/Upload.php`：本地文件上传、分片上传、合并及附件记录。
- `application/common/library/Token.php`：Token 生成、校验及驱动机制。
- `application/common/model/`：用户、配置、附件、日志等公共数据模型。
- `application/index/`：默认前台页面、用户页面和前台 Ajax 接口。

## 请求与部署关系

```text
客户端 / 游戏端
        │
        ▼
public/index.php ──► application/api ──► application/common ──► MySQL(defend)

管理员
        │
        ▼
public/JKZSjbdETB.php ──► application/admin ──► application/common ──► MySQL(defend)

Excel 上传站点
        │
        ▼
后台上传/导入能力 ──► public/uploads、public/template ──► PhpSpreadsheet 解析
```

两个站点的 Web 根目录应指向项目的 `public` 目录；不要将项目根目录直接作为 Web 根目录暴露。

## 配置说明

项目通过 ThinkPHP 环境变量读取数据库配置，配置文件为 `application/database.php`。可参考 `.env.sample` 创建项目根目录下的 `.env`：

```ini
[app]
debug = false
trace = false

[database]
hostname = 127.0.0.1
database = defend
username = defend
password = defend
hostport = 3306
prefix = fa_
```

`application/extra/upload.php` 控制上传目录、大小、文件类型及保存规则；默认上传文件会保存到 `public/uploads/`。生产环境应确认 PHP 的 `upload_max_filesize`、`post_max_size` 和 Web 服务器上传限制满足 Excel 文件大小要求。

## 本地安装与开发

```bash
# 拉取代码
git clone git@github.com:skyfty/CrazyRobotServer.git
cd CrazyRobotServer

# 安装 PHP 依赖
composer install

# 安装前端依赖（仅在需要重新构建前端资源时执行）
npm install
npm run build
```

部署前需完成以下准备：

1. 创建 MySQL 数据库 `defend`，并授予用户 `defend` 对该库的访问权限。
2. 配置 `.env` 或服务器环境变量，确认数据库地址、端口、账号、密码和表前缀。
3. 配置 Web 服务器，将站点根目录指向 `/www/wwwroot/CrazyRobotAdmin/public`。
4. 确认 `runtime/`、`public/uploads/` 具有 Web 进程可写权限。
5. 首次部署时按 FastAdmin 安装流程初始化数据库；已有生产库时不要重复执行安装或覆盖数据。
6. 关闭生产环境调试与错误详情输出，并配置 HTTPS、定时备份和日志清理策略。

## 代码修改与上线交接

后端项目 Git 地址：

```text
git@github.com:skyfty/CrazyRobotServer.git
```

通过ftp账号创建用户，并定位到
`/www/wwwroot/CrazyRobotAdmin`目录下，
修改代码后按目录同步上传到对应目录下即可

建议上线流程：

1. 从 Git 拉取或检出待发布版本。
2. 在测试环境验证 API、后台登录、文件上传和 Excel 导入。
3. 备份数据库、`public/uploads/` 和当前发布目录。
4. 按本地目录结构同步修改后的文件到 `/www/wwwroot/CrazyRobotAdmin`。
5. 如依赖发生变化，在服务器执行 `composer install --no-dev`；前端资源变化时先执行构建再同步构建产物。
6. 清理 ThinkPHP 运行时缓存，检查站点首页、后台入口、API 和 Excel 上传功能。

## 常见排查位置

| 现象 | 优先检查 |
| --- | --- |
| 数据库连接失败 | `.env`、`application/database.php`、MySQL 用户权限和端口 |
| 页面或接口 404 | Web 根目录是否为 `public/`、伪静态/重写规则、`application/route.php` |
| 上传失败 | `application/extra/upload.php`、目录权限、PHP 上传限制、文件类型白名单 |
| Excel 导入失败 | `phpoffice/phpspreadsheet` 是否安装、文件格式、导入模板字段和后台导入方法 |
| 后台无法访问 | `public/JKZSjbdETB.php` 是否存在、安装锁文件、后台权限规则 |
| 修改后仍显示旧内容 | 清理 `runtime/` 缓存并检查 PHP OPcache |

## 安全注意事项

- 后台入口文件名属于安全配置的一部分，不应在公开文档或前端代码中额外暴露。
- 数据库密码、FTP 密码、管理员密码和第三方密钥不要提交到仓库或发送到不受控的群组。
- `public/uploads/` 仅允许业务需要的文件类型，并禁止上传目录执行 PHP 脚本。
- 定期备份数据库和上传文件，发布前先确认备份可恢复。
- 生产环境保持 `app.debug = false`，并限制后台与数据库的访问来源。
