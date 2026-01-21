<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($title) ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bulma@0.9.4/css/bulma.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.snow.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --info-gradient: linear-gradient(135deg, #17a2b8 0%, #20c997 100%);
            --success-gradient: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            --warning-gradient: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%);
            --danger-gradient: linear-gradient(135deg, #dc3545 0%, #e83e8c 100%);
            --dark-gradient: linear-gradient(135deg, #343a40 0%, #495057 100%);
            --card-shadow: 0 4px 20px rgba(0,0,0,0.08);
            --card-hover-shadow: 0 12px 40px rgba(0,0,0,0.12);
        }
        
        body { 
            background: linear-gradient(135deg, #f5f7fa 0%, #e4e8ec 100%);
            min-height: 100vh; 
            display: flex; 
            flex-direction: column; 
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif; 
        }
        .admin-main { flex: 1; }
        
        /* ==================== 全局卡片样式 ==================== */
        .admin-card, .card.admin-card {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(0,0,0,0.04);
            transition: all 0.3s ease;
        }
        .admin-card:hover {
            box-shadow: var(--card-hover-shadow);
        }
        
        /* ==================== 页面头部样式 ==================== */
        .page-header {
            background: var(--primary-gradient);
            border-radius: 16px;
            padding: 1.5rem 2rem;
            margin-bottom: 2rem;
            color: white;
            box-shadow: 0 10px 40px rgba(102, 126, 234, 0.3);
        }
        .page-header h1,
        .page-header .title {
            color: white !important;
            margin-bottom: 0.5rem !important;
            font-weight: 700;
            line-height: 1.3;
        }
        .page-header .subtitle {
            color: rgba(255,255,255,0.85);
            margin-top: 0 !important;
            margin-bottom: 0 !important;
            line-height: 1.4;
        }
        .page-header .header-actions .button {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
        }
        .page-header .header-actions .button:hover {
            background: rgba(255,255,255,0.3);
        }
        .page-header .header-actions .button.is-white {
            background: white;
            color: #667eea;
        }
        
        /* ==================== 统一表格样式 ==================== */
        .modern-table {
            background: white;
            border-radius: 16px;
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(0,0,0,0.04);
            overflow: hidden;
        }
        .modern-table .table {
            margin-bottom: 0;
        }
        .modern-table .table thead th {
            background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 2px solid #e2e8f0;
            color: #475569;
            font-weight: 600;
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 1rem 1.25rem;
        }
        .modern-table .table tbody td {
            padding: 1rem 1.25rem;
            vertical-align: middle;
            border-bottom: 1px solid #f1f5f9;
        }
        .modern-table .table tbody tr:hover {
            background: #f8fafc;
        }
        .modern-table .table tbody tr:last-child td {
            border-bottom: none;
        }
        
        /* ==================== 统一表单样式 ==================== */
        .modern-form .field .label {
            font-weight: 600;
            color: #374151;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }
        .modern-form .input,
        .modern-form .textarea,
        .modern-form .select select {
            border-radius: 10px;
            border: 2px solid #e5e7eb;
            box-shadow: none;
            transition: all 0.2s ease;
            font-size: 0.9375rem;
        }
        .modern-form .input:focus,
        .modern-form .textarea:focus,
        .modern-form .select select:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .modern-form .input:hover,
        .modern-form .textarea:hover,
        .modern-form .select select:hover {
            border-color: #d1d5db;
        }
        
        /* ==================== 统一按钮样式 ==================== */
        .button {
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .button.is-primary, .button.is-link {
            background: var(--primary-gradient);
            border: none;
            color: #fff !important;
        }
        .button.is-primary:hover, .button.is-link:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            color: #fff !important;
        }
        .button.is-success {
            background: var(--success-gradient);
            border: none;
            color: #fff !important;
        }
        .button.is-success:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.4);
            color: #fff !important;
        }
        .button.is-info {
            background: var(--info-gradient);
            border: none;
            color: #fff !important;
        }
        .button.is-info:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(23, 162, 184, 0.4);
            color: #fff !important;
        }
        .button.is-warning {
            background: var(--warning-gradient);
            border: none;
            color: #1a1a1a !important;
        }
        .button.is-warning:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(255, 193, 7, 0.4);
            color: #1a1a1a !important;
        }
        .button.is-danger {
            background: var(--danger-gradient);
            border: none;
            color: #fff !important;
        }
        .button.is-danger:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.4);
            color: #fff !important;
        }
        .button.is-dark {
            background: var(--dark-gradient);
            border: none;
            color: #fff !important;
        }
        .button.is-dark:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(52, 58, 64, 0.4);
            color: #fff !important;
        }
        /* Light 变体按钮 - 浅色背景深色文字 */
        .button.is-primary.is-light {
            background: rgba(102, 126, 234, 0.15);
            color: #5a67d8 !important;
        }
        .button.is-primary.is-light:hover {
            background: rgba(102, 126, 234, 0.25);
            color: #4c51bf !important;
            box-shadow: none;
            transform: none;
        }
        .button.is-link.is-light {
            background: rgba(102, 126, 234, 0.15);
            color: #5a67d8 !important;
        }
        .button.is-link.is-light:hover {
            background: rgba(102, 126, 234, 0.25);
            color: #4c51bf !important;
            box-shadow: none;
            transform: none;
        }
        .button.is-success.is-light {
            background: rgba(40, 167, 69, 0.15);
            color: #1e7e34 !important;
        }
        .button.is-success.is-light:hover {
            background: rgba(40, 167, 69, 0.25);
            color: #155724 !important;
            box-shadow: none;
            transform: none;
        }
        .button.is-info.is-light {
            background: rgba(23, 162, 184, 0.15);
            color: #138496 !important;
        }
        .button.is-info.is-light:hover {
            background: rgba(23, 162, 184, 0.25);
            color: #0c5460 !important;
            box-shadow: none;
            transform: none;
        }
        .button.is-warning.is-light {
            background: rgba(255, 193, 7, 0.15);
            color: #856404 !important;
        }
        .button.is-warning.is-light:hover {
            background: rgba(255, 193, 7, 0.25);
            color: #6c5300 !important;
            box-shadow: none;
            transform: none;
        }
        .button.is-danger.is-light {
            background: rgba(220, 53, 69, 0.15);
            color: #c82333 !important;
        }
        .button.is-danger.is-light:hover {
            background: rgba(220, 53, 69, 0.25);
            color: #a71d2a !important;
            box-shadow: none;
            transform: none;
        }
        .button.is-dark.is-light {
            background: rgba(52, 58, 64, 0.1);
            color: #343a40 !important;
        }
        .button.is-dark.is-light:hover {
            background: rgba(52, 58, 64, 0.2);
            color: #1d2124 !important;
            box-shadow: none;
            transform: none;
        }
        .button.is-light {
            background: #f8f9fa;
            color: #495057 !important;
            border: 1px solid #e9ecef;
        }
        .button.is-light:hover {
            background: #e9ecef;
            color: #343a40 !important;
        }
        .button.is-white {
            background: #fff;
            color: #363636 !important;
            border: 1px solid #dbdbdb;
        }
        .button.is-white:hover {
            background: #f5f5f5;
            color: #363636 !important;
        }
        
        /* ==================== 统一标签样式 ==================== */
        .tag {
            border-radius: 50px;
            font-weight: 600;
            padding: 0.5em 1em;
        }
        .tag.is-primary { background: rgba(102, 126, 234, 0.15); color: #667eea; }
        .tag.is-info { background: rgba(23, 162, 184, 0.15); color: #17a2b8; }
        .tag.is-success { background: rgba(40, 167, 69, 0.15); color: #28a745; }
        .tag.is-warning { background: rgba(255, 193, 7, 0.15); color: #d39e00; }
        .tag.is-danger { background: rgba(220, 53, 69, 0.15); color: #dc3545; }
        
        /* ==================== 统一Tabs样式 ==================== */
        .modern-tabs {
            background: white;
            border-radius: 12px;
            padding: 0.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
            margin-bottom: 1.5rem;
            display: inline-flex;
            gap: 0.25rem;
        }
        .modern-tabs a {
            padding: 0.625rem 1.25rem;
            border-radius: 8px;
            color: #64748b;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.2s;
            text-decoration: none;
        }
        .modern-tabs a:hover {
            background: #f1f5f9;
            color: #334155;
        }
        .modern-tabs a.is-active {
            background: var(--primary-gradient);
            color: white;
        }
        
        /* ==================== 分区标题 ==================== */
        .section-title {
            font-size: 1.125rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 1.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        .section-title .icon-box {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }
        .section-title .icon-box.primary { background: rgba(102, 126, 234, 0.15); color: #667eea; }
        .section-title .icon-box.info { background: rgba(23, 162, 184, 0.15); color: #17a2b8; }
        .section-title .icon-box.success { background: rgba(40, 167, 69, 0.15); color: #28a745; }
        .section-title .icon-box.warning { background: rgba(255, 193, 7, 0.15); color: #d39e00; }
        .section-title .icon-box.danger { background: rgba(220, 53, 69, 0.15); color: #dc3545; }
        
        /* ==================== 空状态 ==================== */
        .empty-state {
            text-align: center;
            padding: 3rem 2rem;
            color: #94a3b8;
        }
        .empty-state .icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }
        .empty-state p {
            font-size: 1rem;
        }
        
        /* ==================== 导航样式优化 ==================== */
        .admin-navbar { 
            background: white;
            border-bottom: 1px solid #edf2f7; 
            box-shadow: 0 2px 8px rgba(0,0,0,0.04); 
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .admin-navbar .navbar-brand {
            align-items: center;
            min-height: 3.5rem;
        }
        .admin-navbar .navbar-brand .navbar-item {
            padding: 0.5rem;
        }
        .admin-navbar .navbar-brand .navbar-item img {
            max-height: 2.25rem;
        }
        .admin-navbar .navbar-burger {
            color: #64748b;
            height: 3.5rem;
            width: 3.5rem;
        }
        .admin-navbar .navbar-burger:hover {
            background: #f1f5f9;
            color: #334155;
        }
        .admin-navbar .navbar-menu .navbar-item {
            font-weight: 500;
            color: #64748b;
            border-radius: 8px;
            margin: 0 0.125rem;
            transition: all 0.2s;
           
        }
        .admin-navbar .navbar-menu .navbar-item:hover {
            background: #f1f5f9;
            color: #334155;
        }
        .admin-navbar .navbar-menu .navbar-item.is-active {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
        }
        .admin-navbar .navbar-start {
            gap: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .admin-subnav { background: #fff; border-bottom: 1px solid #edf2f7; padding: 0; margin-bottom: 1.5rem; }
        .admin-subnav .container { display: flex; align-items: stretch; overflow-x: auto; white-space: nowrap; -webkit-overflow-scrolling: touch; }
        .admin-subnav .navbar-item { 
            font-size: 0.875rem; 
            color: #64748b; 
            padding: 0.875rem 1.25rem;
            border-bottom: 3px solid transparent;
            transition: all 0.2s;
            font-weight: 500;
        }
        .admin-subnav .navbar-item:hover { background: #f8fafc; color: #667eea; }
        .admin-subnav .navbar-item.is-active { 
            color: #667eea; 
            font-weight: 600; 
            border-bottom-color: #667eea;
            background: linear-gradient(180deg, rgba(102, 126, 234, 0.05) 0%, transparent 100%);
        }
        
        /* 隐藏滚动条但保持功能 */
        .admin-subnav .container::-webkit-scrollbar { display: none; }
        .admin-subnav .container { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* ==================== 移动端响应式 ==================== */
        @media screen and (max-width: 1023px) {
            .admin-navbar .navbar-menu {
                background: white;
                box-shadow: 0 8px 16px rgba(0,0,0,0.1);
                border-radius: 0 0 12px 12px;
                padding: 0.5rem;
            }
            .admin-navbar .navbar-menu.is-active {
                display: block;
            }
            .admin-navbar .navbar-menu .navbar-item {
                border-radius: 8px;
                padding: 0.75rem 1rem;
                margin: 0.125rem 0;
            }
            .admin-navbar .navbar-start {
                flex-direction: column;
                gap: 0;
            }
            .admin-navbar .navbar-end {
                border-top: 1px solid #e5e7eb;
                padding-top: 0.5rem;
                margin-top: 0.5rem;
            }
            .admin-navbar .navbar-dropdown {
                position: static;
                box-shadow: none;
                border: none;
                padding-left: 1rem;
            }
            .admin-navbar .navbar-link::after {
                display: none;
            }
            .admin-subnav .container {
                padding: 0 0.5rem;
            }
            .admin-subnav .navbar-item {
                padding: 0.75rem 1rem;
                font-size: 0.8125rem;
            }
            .page-header {
                padding: 1.25rem 1.5rem;
                margin-bottom: 1.5rem;
            }
            .page-header .level {
                flex-direction: column;
                align-items: flex-start !important;
            }
            .page-header .level-right {
                margin-top: 1rem;
            }
        }
        
        @media screen and (max-width: 768px) {
            .section {
                padding: 1.5rem 1rem;
            }
            .page-header {
                border-radius: 12px;
                padding: 1rem 1.25rem;
            }
            .page-header .title {
                font-size: 1.25rem !important;
            }
            .admin-card {
                border-radius: 12px;
            }
            .modern-table {
                border-radius: 12px;
            }
            .modern-tabs {
                width: 100%;
                overflow-x: auto;
                flex-wrap: nowrap;
                -webkit-overflow-scrolling: touch;
            }
            .modern-tabs::-webkit-scrollbar { display: none; }
        }

        .relative{position:relative}
        .media-select-item.is-selected{background-color:#f0f7ff; border-color: #FF5722 !important;}
        
        /* 媒体网格样式 */
        #media-container { display: grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 10px; }
        .media-item { aspect-ratio: 1/1; border: 2px solid #e5e7eb; border-radius: 12px; overflow: hidden; background: #fff; cursor: grab; position: relative; transition: all 0.2s; }
        .media-item:hover { border-color: #667eea; }
        .media-item img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .media-item .delete { position: absolute; top: 8px; right: 8px; z-index: 10; display: none; }
        .media-item:hover .delete { display: block; }

        .media-add-btn { aspect-ratio: 1/1; border: 2px dashed #d1d5db; border-radius: 12px; display: flex; align-items: center; justify-content: center; cursor: pointer; background: #fafafa; transition: all .2s; }
        .media-add-btn:hover { border-color: #667eea; background: rgba(102, 126, 234, 0.05); }
        
        .media-placeholder { border: 2px dashed #d1d5db; border-radius: 12px; padding: 40px; text-align: center; background: #fafafa; }
        .media-placeholder .buttons { justify-content: center; margin-bottom: 10px; }
        
        .ql-editor { font-size: 16px !important; min-height: 200px; }
        .ql-container { border-radius: 0 0 10px 10px; border-color: #e5e7eb; }
        .ql-toolbar { border-radius: 10px 10px 0 0; border-color: #e5e7eb; background: #f9fafb; }

        /* 响应式表格 */
        .table-container { overflow-x: auto; }
        
        /* ==================== 动画 ==================== */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        .animate-in {
            animation: fadeInUp 0.4s ease forwards;
        }
        .delay-1 { animation-delay: 0.1s; opacity: 0; }
        .delay-2 { animation-delay: 0.2s; opacity: 0; }
        .delay-3 { animation-delay: 0.3s; opacity: 0; }
        
        /* ==================== 统计卡片 ==================== */
        .stat-mini {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 12px;
        }
        .stat-mini .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: white;
        }
        .stat-mini .stat-info .value {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            line-height: 1;
        }
        .stat-mini .stat-info .label {
            font-size: 0.75rem;
            color: #64748b;
            margin-top: 0.25rem;
        }
        
        /* ==================== 下拉菜单优化 ==================== */
        .dropdown-content {
            border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.12);
            border: 1px solid #e5e7eb;
            padding: 0.5rem;
        }
        .dropdown-item {
            border-radius: 8px;
            padding: 0.625rem 1rem;
        }
        .dropdown-item:hover {
            background: #f1f5f9;
        }
        
        /* ==================== 模态框优化 ==================== */
        .modal-card {
            border-radius: 20px;
            overflow: hidden;
        }
        .modal-card-head {
            background: linear-gradient(180deg, #f8fafc 0%, #fff 100%);
            border-bottom: 1px solid #e5e7eb;
        }
        .modal-card-title {
            font-weight: 700;
        }
        .modal-card-foot {
            background: #f8fafc;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <?php if ($showNav ?? true): 
    $current_path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    $active_group = '';
    if (str_starts_with($current_path, '/admin/products') || str_starts_with($current_path, '/admin/product-categories')) $active_group = 'catalog';
    elseif (str_starts_with($current_path, '/admin/cases')) $active_group = 'cases';
    elseif (str_starts_with($current_path, '/admin/posts') || str_starts_with($current_path, '/admin/post-categories')) $active_group = 'blog';
    elseif (str_starts_with($current_path, '/admin/messages') || str_starts_with($current_path, '/admin/inquiries')) $active_group = 'inbox';
    elseif (str_starts_with($current_path, '/admin/staff')) $active_group = 'staff';

    $user_role = $_SESSION['admin_role'] ?? 'staff';
    $user_perms = $_SESSION['admin_permissions'] ?? [];
    
    $can_access = function($perm) use ($user_role, $user_perms) {
        return $user_role === 'admin' || in_array($perm, $user_perms);
    };
?>
    <!-- 第一级主导航 -->
    <nav class="navbar is-white admin-navbar" role="navigation" aria-label="main navigation">
        <div class="container">
            <div class="navbar-brand">
                <a class="logo-link" href="/admin" style="padding: 0.5rem;">
                    <img src="https://www.shopagg.com/wp-content/uploads/2024/12/shopagg-logo-b.png" alt="logo" style="height: 36px; max-height: 36px;">
                </a>
                <a role="button" class="navbar-burger" aria-label="menu" aria-expanded="false" data-target="adminNavbar">
                    <span aria-hidden="true"></span>
                    <span aria-hidden="true"></span>
                    <span aria-hidden="true"></span>
                </a>
            </div>

            <div id="adminNavbar" class="navbar-menu ml-6">
                <div class="navbar-start">
                    <a class="navbar-item <?= $current_path === '/admin' ? 'is-active' : '' ?>" href="/admin">
                        <span class="icon mr-1"><i class="fas fa-home"></i></span>仪表盘
                    </a>
                    <?php if ($can_access('inbox')): ?>
                    <a class="navbar-item <?= $active_group === 'inbox' ? 'is-active' : '' ?>" href="/admin/messages">
                        <span class="icon mr-1"><i class="fas fa-envelope"></i></span>收件箱
                    </a>
                    <?php endif; ?>
                    <?php if ($can_access('products')): ?>
                    <a class="navbar-item <?= $active_group === 'catalog' ? 'is-active' : '' ?>" href="/admin/products">
                        <span class="icon mr-1"><i class="fas fa-box"></i></span>产品中心
                    </a>
                    <?php endif; ?>
                    <?php if ($can_access('cases')): ?>
                    <a class="navbar-item <?= $active_group === 'cases' ? 'is-active' : '' ?>" href="/admin/cases">
                        <span class="icon mr-1"><i class="fas fa-briefcase"></i></span>案例展示
                    </a>
                    <?php endif; ?>
                    <?php if ($can_access('blog')): ?>
                    <a class="navbar-item <?= $active_group === 'blog' ? 'is-active' : '' ?>" href="/admin/posts">
                        <span class="icon mr-1"><i class="fas fa-pen-nib"></i></span>内容管理
                    </a>
                    <?php endif; ?>
                    
                    <?php if ($user_role === 'admin'): ?>
                    <a class="navbar-item <?= $active_group === 'staff' ? 'is-active' : '' ?>" href="/admin/staff">
                        <span class="icon mr-1"><i class="fas fa-users"></i></span>员工管理
                    </a>
                    <?php endif; ?>

                    <?php if ($can_access('settings')): ?>
                    <a class="navbar-item <?= $current_path === '/admin/settings' ? 'is-active' : '' ?>" href="/admin/settings">
                        <span class="icon mr-1"><i class="fas fa-cog"></i></span>系统设置
                    </a>
                    <?php endif; ?>
                </div>

                <div class="navbar-end">
                    <div class="navbar-item has-dropdown is-hoverable">
                        <a class="navbar-link">
                            <span class="icon mr-1"><i class="fas fa-user-circle"></i></span>
                            <?= h($_SESSION['admin_display_name'] ?? $_SESSION['admin_user'] ?? 'Admin') ?>
                        </a>
                        <div class="navbar-dropdown is-right">
                            <a class="navbar-item" href="/admin/profile">
                                <span class="icon mr-2"><i class="fas fa-id-card"></i></span>个人资料
                            </a>
                            <a class="navbar-item" href="/" target="_blank">
                                <span class="icon mr-2"><i class="fas fa-external-link-alt"></i></span>访问网站
                            </a>
                            <hr class="navbar-divider">
                            <a class="navbar-item has-text-danger" href="/admin/logout">
                                <span class="icon mr-2"><i class="fas fa-sign-out-alt"></i></span>退出登录
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- 第二级二级导航 -->
    <?php if ($active_group): ?>
    <nav class="admin-subnav">
        <div class="container admin-subnav-container">
            <?php if ($active_group === 'catalog'): ?>
                <a class="navbar-item <?= $current_path === '/admin/products' || str_contains($current_path, '/admin/products/') ? 'is-active' : '' ?>" href="/admin/products">
                    <span class="icon mr-1"><i class="fas fa-list"></i></span>产品列表
                </a>
                <a class="navbar-item <?= str_contains($current_path, '/admin/product-categories') ? 'is-active' : '' ?>" href="/admin/product-categories">
                    <span class="icon mr-1"><i class="fas fa-folder"></i></span>产品分类
                </a>
            <?php elseif ($active_group === 'cases'): ?>
                <a class="navbar-item <?= $current_path === '/admin/cases' ? 'is-active' : '' ?>" href="/admin/cases">
                    <span class="icon mr-1"><i class="fas fa-list"></i></span>所有案例
                </a>
                <a class="navbar-item <?= str_contains($current_path, '/create') ? 'is-active' : '' ?>" href="/admin/cases/create">
                    <span class="icon mr-1"><i class="fas fa-plus"></i></span>新增案例
                </a>
            <?php elseif ($active_group === 'blog'): ?>
                <a class="navbar-item <?= $current_path === '/admin/posts' || str_contains($current_path, '/admin/posts/') ? 'is-active' : '' ?>" href="/admin/posts">
                    <span class="icon mr-1"><i class="fas fa-list"></i></span>文章列表
                </a>
                <a class="navbar-item <?= str_contains($current_path, '/admin/post-categories') ? 'is-active' : '' ?>" href="/admin/post-categories">
                    <span class="icon mr-1"><i class="fas fa-folder"></i></span>文章分类
                </a>
            <?php elseif ($active_group === 'inbox'): ?>
                <a class="navbar-item <?= str_contains($current_path, '/messages') ? 'is-active' : '' ?>" href="/admin/messages">
                    <span class="icon mr-1"><i class="fas fa-comment-dots"></i></span>联系留言
                </a>
                <a class="navbar-item <?= str_contains($current_path, '/inquiries') ? 'is-active' : '' ?>" href="/admin/inquiries">
                    <span class="icon mr-1"><i class="fas fa-file-invoice"></i></span>询单管理
                </a>
            <?php elseif ($active_group === 'staff'): ?>
                <a class="navbar-item <?= $current_path === '/admin/staff' ? 'is-active' : '' ?>" href="/admin/staff">
                    <span class="icon mr-1"><i class="fas fa-list"></i></span>员工列表
                </a>
                <a class="navbar-item <?= str_contains($current_path, '/create') ? 'is-active' : '' ?>" href="/admin/staff/create">
                    <span class="icon mr-1"><i class="fas fa-user-plus"></i></span>新增员工
                </a>
            <?php endif; ?>
        </div>
    </nav>
    <?php endif; ?>

<?php endif; ?>

<div class="admin-main">
    <section class="section">
        <div class="container">
            <?= $content ?>
        </div>
    </section>
</div>

<!-- 统一媒体库模态框 -->
<div class="modal" id="media-library-modal">
    <div class="modal-background"></div>
    <div class="modal-card" style="width: 90%; max-width: 1000px;">
        <header class="modal-card-head">
            <p class="modal-card-title">
                <span class="icon mr-2"><i class="fas fa-images"></i></span>
                选择媒体文件
            </p>
            <div class="field mb-0 mr-3">
                <div class="control">
                    <div class="file is-info is-small">
                        <label class="file-label">
                            <input class="file-input" type="file" id="media-upload-input" multiple accept="image/*">
                            <span class="file-cta" style="border-radius: 8px;">
                                <span class="file-icon"><i class="fas fa-upload"></i></span>
                                <span class="file-label">上传图片</span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>
            <button type="button" class="delete close-modal" aria-label="close"></button>
        </header>
        <section class="modal-card-body">
            <div id="upload-progress-container" class="mb-4 is-hidden">
                <div class="is-size-7 mb-1 is-flex is-justify-content-between">
                    <span id="upload-status-text">正在上传...</span>
                    <span id="upload-progress-percent">0%</span>
                </div>
                <progress id="overall-progress" class="progress is-info is-small" value="0" max="100" style="border-radius: 50px;">0%</progress>
            </div>
            <div id="media-library-list" class="columns is-multiline is-mobile">
                <!-- 动态加载图片列表 -->
            </div>
        </section>
        <footer class="modal-card-head" style="justify-content: flex-end; border-top: 1px solid #e5e7eb; border-bottom: none;">
            <button type="button" class="button is-link" id="confirm-media-selection" style="display:none">确认选择</button>
            <button type="button" class="button is-light close-modal ml-2">取消</button>
        </footer>
    </div>
</div>

<footer class="footer has-background-white py-4" style="border-top: 1px solid #e5e7eb;">
    <div class="container has-text-centered">
        <p class="is-size-7 has-text-grey">
            &copy; <?= date('Y') ?> SHOPAGG B2B Management Platform. Powered by <a href="https://www.shopagg.com" target="_blank" style="color: #667eea;">SHOPAGG</a>.
        </p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/quill@1.3.7/dist/quill.min.js"></script>
<script>
// 全局媒体库逻辑
let mediaLibraryCallback = null;
let isMultiSelect = false;

function openMediaLibrary(callback, multi = false) {
    mediaLibraryCallback = callback;
    isMultiSelect = multi;
    const modal = document.getElementById('media-library-modal');
    const confirmBtn = document.getElementById('confirm-media-selection');
    
    confirmBtn.style.display = multi ? 'inline-flex' : 'none';
    modal.classList.add('is-active');
    fetchMediaLibrary();
}

async function fetchMediaLibrary() {
    const container = document.getElementById('media-library-list');
    container.innerHTML = '<div class="column is-12 has-text-centered p-6"><span class="icon is-large has-text-grey-light"><i class="fas fa-spinner fa-pulse fa-2x"></i></span><p class="mt-3 has-text-grey">正在加载...</p></div>';
    try {
        const res = await fetch('/admin/media-library');
        const files = await res.json();
        container.innerHTML = '';
        if (files.length === 0) {
            container.innerHTML = '<div class="column is-12 has-text-centered p-6"><span class="icon is-large has-text-grey-light"><i class="fas fa-images fa-2x"></i></span><p class="mt-3 has-text-grey">暂无媒体文件</p></div>';
            return;
        }
        files.forEach(file => {
            const col = document.createElement('div');
            col.className = 'column is-2-desktop is-3-tablet is-4-mobile';
            col.innerHTML = `
                <div class="card media-select-item" data-url="${file}" style="cursor: pointer; border: 4px solid transparent; border-radius: 12px; overflow:hidden; transition: all 0.2s;">
                    <div class="card-image">
                        <figure class="image is-1by1">
                            <img src="${file}" style="object-fit: cover;">
                        </figure>
                    </div>
                </div>
            `;
            col.querySelector('.media-select-item').addEventListener('click', function() {
                if (isMultiSelect) {
                    this.classList.toggle('is-selected');
                } else {
                    if (mediaLibraryCallback) mediaLibraryCallback(this.dataset.url);
                    document.getElementById('media-library-modal').classList.remove('is-active');
                }
            });
            container.appendChild(col);
        });
    } catch (err) {
        container.innerHTML = '<div class="column is-12 has-text-centered p-6"><span class="icon is-large has-text-danger"><i class="fas fa-exclamation-circle fa-2x"></i></span><p class="mt-3 has-text-danger">加载失败</p></div>';
    }
}

document.addEventListener("DOMContentLoaded", function () {
    // 手机端导航切换
    const $navbarBurgers = Array.prototype.slice.call(document.querySelectorAll('.navbar-burger'), 0);
    $navbarBurgers.forEach( el => {
        el.addEventListener('click', () => {
            const target = el.dataset.target;
            const $target = document.getElementById(target);
            el.classList.toggle('is-active');
            $target.classList.toggle('is-active');
        });
    });

    // 媒体库通用事件
    const modal = document.getElementById('media-library-modal');
    const closeBtns = document.querySelectorAll('.close-modal, .modal-background');
    closeBtns.forEach(btn => btn.addEventListener('click', () => modal.classList.remove('is-active')));

    const confirmBtn = document.getElementById('confirm-media-selection');
    confirmBtn.addEventListener('click', () => {
        const selected = Array.from(document.querySelectorAll('.media-select-item.is-selected')).map(el => el.dataset.url);
        if (mediaLibraryCallback) mediaLibraryCallback(selected);
        modal.classList.remove('is-active');
    });

    const uploadInput = document.getElementById('media-upload-input');
    if (uploadInput) {
        uploadInput.addEventListener('change', async function() {
            const files = Array.from(this.files);
            if (files.length === 0) return;
            const progressContainer = document.getElementById('upload-progress-container');
            const overallProgress = document.getElementById('overall-progress');
            const progressPercent = document.getElementById('upload-progress-percent');
            const statusText = document.getElementById('upload-status-text');

            progressContainer.classList.remove('is-hidden');
            overallProgress.value = 0;
            progressPercent.innerText = '0%';
            
            const fileProgresses = new Array(files.length).fill(0);
            const uploadTasks = files.map((file, index) => {
                return new Promise((resolve, reject) => {
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', '/admin/upload-image', true);
                    xhr.upload.onprogress = (e) => {
                        if (e.lengthComputable) {
                            fileProgresses[index] = e.loaded / e.total;
                            const totalProgress = fileProgresses.reduce((a, b) => a + b, 0) / files.length;
                            const percent = Math.round(totalProgress * 100);
                            overallProgress.value = percent;
                            progressPercent.innerText = percent + '%';
                        }
                    };
                    xhr.onload = () => resolve();
                    xhr.onerror = () => reject();
                    const formData = new FormData();
                    formData.append('image', file);
                    formData.append('csrf', '<?= csrf_token() ?>');
                    xhr.send(formData);
                });
            });

            try {
                await Promise.all(uploadTasks);
                setTimeout(() => {
                    progressContainer.classList.add('is-hidden');
                    fetchMediaLibrary();
                }, 1000);
            } catch (err) { alert('上传失败'); }
            this.value = '';
        });
    }

    // 1. Quill Editor
    const editor = document.getElementById("quill-editor");
    const input = document.getElementById("content-input");
    if (editor && input) {
        const quill = new Quill(editor, {
            theme: "snow",
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'align': [] }],
                    ['blockquote', 'code-block'],
                    ['link', 'image'],
                    ['clean']
                ]
            }
        });
        quill.root.innerHTML = input.value || "";
        const form = input.closest("form");
        form.addEventListener("submit", function () {
            input.value = quill.root.innerHTML;
        });

        // 调用统一媒体库（支持多选图片）
        const toolbar = quill.getModule("toolbar");
        toolbar.addHandler("image", function () {
            openMediaLibrary(function(urls) {
                const range = quill.getSelection(true);
                let index = range ? range.index : 0;
                urls.forEach(url => {
                    quill.insertEmbed(index, "image", url);
                    index += 1;
                });
            }, true);
        });
    }

    // 2. Product Image Preview
    const imageInput = document.querySelector("input[name='images[]']");
    const preview = document.getElementById("product-image-preview");
    if (imageInput && preview) {
        imageInput.addEventListener("change", function () {
            preview.innerHTML = "";
            Array.from(imageInput.files).slice(0, 6).forEach(file => {
                const reader = new FileReader();
                reader.onload = e => {
                    const div = document.createElement("div");
                    div.className = "column is-3";
                    div.innerHTML = `<figure class="image"><img src="${e.target.result}"></figure>`;
                    preview.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        });
    }

    // 3. Price Tiers
    const addTierBtn = document.getElementById("add-price-tier");
    const tierWrap = document.getElementById("price-tier-wrap");
    if (addTierBtn && tierWrap) {
        addTierBtn.addEventListener("click", function () {
            const row = document.createElement("div");
            row.className = "columns price-tier-row";
            row.innerHTML = `
                <div class="column"><div class="field"><label class="label is-size-7">最小数量</label><div class="control"><input class="input" name="price_min[]" type="number" min="1" required></div></div></div>
                <div class="column"><div class="field"><label class="label is-size-7">最大数量</label><div class="control"><input class="input" name="price_max[]" type="number" min="1" placeholder="可空"></div></div></div>
                <div class="column"><div class="field"><label class="label is-size-7">单价</label><div class="control"><input class="input" name="price_value[]" type="number" min="0" step="0.01" required></div></div></div>
                <div class="column"><div class="field"><label class="label is-size-7">货币</label><div class="control"><input class="input" name="price_currency[]" value="USD" required></div></div></div>
                <div class="column is-narrow"><div class="field"><label class="label is-size-7">操作</label><div class="control"><button type="button" class="button is-light remove-price-tier">删除</button></div></div></div>
            `;
            tierWrap.appendChild(row);
        });
        tierWrap.addEventListener("click", function (e) {
            if (e.target.classList.contains("remove-price-tier")) {
                e.target.closest(".price-tier-row").remove();
            }
        });
    }
});
</script>
</body>
</html>
