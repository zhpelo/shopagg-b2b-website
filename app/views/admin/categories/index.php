<?php
$type = $type ?? 'product';
$isPost = $type === 'post';
$themeColor = $isPost ? '#48c774' : '#ffc107';
$themeGradient = $isPost ? 'linear-gradient(135deg, #48c774 0%, #00d1b2 100%)' : 'linear-gradient(135deg, #ffc107 0%, #fd7e14 100%)';
$icon = $isPost ? 'fa-newspaper' : 'fa-folder';
$label = $isPost ? '文章分类' : '产品分类';
$baseUrl = $base_url ?? ($isPost ? '/admin/post-categories' : '/admin/product-categories');
$backUrl = $isPost ? '/admin/posts' : '/admin/products';

// 递归渲染树形结构
function renderCategoryTree($items, $type, $themeGradient, $icon, $categoryModel, $baseUrl)
{
    if (empty($items)) return;
    foreach ($items as $row):
        $level = $row['level'] ?? 0;
        $hasChildren = !empty($row['children']);
        $itemCount = $categoryModel->countItems((int)$row['id'], $type);
?>
        <tr class="category-row level-<?= $level ?>" data-id="<?= (int)$row['id'] ?>" data-parent="<?= (int)$row['parent_id'] ?>">
            <td>
                <div class="flex items-center" style="padding-left: <?= $level * 28 ?>px;">
                    <?php if ($level > 0): ?>
                        <span class="tree-line" style="color: #cbd5e0; margin-right: 8px;">
                            <i class="fas fa-level-up-alt fa-rotate-90"></i>
                        </span>
                    <?php endif; ?>
                    <div style="width: 40px; height: 40px; background: <?= $themeGradient ?>; border-radius: 10px; margin-right: 1rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <span class="icon has-text-white"><i class="fas <?= $icon ?>"></i></span>
                    </div>
                    <div>
                        <strong><?= h($row['name']) ?></strong>
                        <?php if ($hasChildren): ?>
                            <span class="ml-2 inline-flex rounded-full bg-slate-100 px-2.5 py-1 text-[11px] font-semibold text-slate-500"><?= count($row['children']) ?> 个子分类</span>
                        <?php endif; ?>
                        <?php if (!empty($row['description'])): ?>
                            <p class="mt-1 text-xs text-slate-500"><?= h($row['description']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </td>
            <td>
                <code style="background: #f1f5f9; padding: 0.25rem 0.75rem; border-radius: 6px; font-size: 0.8125rem;"><?= h($row['slug']) ?></code>
            </td>
            <td>
                <span class="inline-flex rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-600"><?= $itemCount ?> 项</span>
            </td>
            <td>
                <span class="text-xs text-slate-500">
                    <span class="icon is-small"><i class="far fa-calendar-alt"></i></span>
                    <?= format_date($row['created_at'], 'Y-m-d') ?>
                </span>
            </td>
            <td>
                <div class="flex flex-wrap gap-2">
                    <a href="<?= $baseUrl ?>/edit?id=<?= (int)$row['id'] ?>" class="inline-flex items-center gap-2 rounded-xl border border-slate-200 bg-white px-3 py-2 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                        <span class="icon"><i class="fas fa-edit"></i></span>
                        <span>编辑</span>
                    </a>
                    <a href="<?= $baseUrl ?>/delete?id=<?= (int)$row['id'] ?>" class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-rose-50 text-rose-600 transition hover:bg-rose-100" onclick="return confirm('确定要删除该分类吗？子分类将提升到父级。')">
                        <span class="icon"><i class="fas fa-trash-alt"></i></span>
                    </a>
                </div>
            </td>
        </tr>
<?php
        // 递归渲染子分类
        if ($hasChildren) {
            renderCategoryTree($row['children'], $type, $themeGradient, $icon, $categoryModel, $baseUrl);
        }
    endforeach;
}

// 统计总数
function countTotalCategories($items)
{
    $count = count($items);
    foreach ($items as $item) {
        if (!empty($item['children'])) {
            $count += countTotalCategories($item['children']);
        }
    }
    return $count;
}
$totalCount = countTotalCategories($categories);
?>

<!-- 页面头部 -->
<div class="page-header" style="background: <?= $themeGradient ?>; box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
        <div class="flex items-center gap-4">
            <div>
                <h1 class="flex items-center gap-2 text-2xl font-bold text-white">
                    <span class="icon mr-2"><i class="fas <?= $icon ?>"></i></span>
                    <?= $label ?>
                </h1>
                <p class="mt-1 text-sm text-white/80">共有 <?= $totalCount ?> 个分类</p>
            </div>
        </div>
        <div class="header-actions flex flex-wrap items-center gap-3">
            <a href="<?= $backUrl ?>" class="inline-flex items-center gap-2 rounded-xl border border-white/50 bg-white/10 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-white/20">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>返回<?= $isPost ? '文章' : '产品' ?>列表</span>
            </a>
            <a href="<?= $baseUrl ?>/create" class="inline-flex items-center gap-2 rounded-xl bg-white px-4 py-2.5 text-sm font-semibold <?= $isPost ? 'text-emerald-600' : 'text-amber-700' ?> shadow-sm transition hover:bg-slate-50">
                <span class="icon"><i class="fas fa-plus"></i></span>
                <span>新建分类</span>
            </a>
        </div>
    </div>
</div>

<?php if (empty($categories)): ?>
    <!-- 空状态 -->
    <div class="admin-card">
        <div class="empty-state">
            <span class="icon"><i class="fas fa-folder-open"></i></span>
            <p>暂无<?= $label ?>记录</p>
            <a href="<?= $baseUrl ?>/create" class="mt-4 inline-flex items-center gap-2 rounded-xl <?= $isPost ? 'bg-gradient-to-r from-emerald-500 to-teal-500 shadow-emerald-500/25' : 'bg-gradient-to-r from-amber-400 to-orange-500 shadow-amber-500/25' ?> px-4 py-2.5 text-sm font-semibold text-white shadow-lg transition hover:-translate-y-0.5">
                <span class="icon"><i class="fas fa-plus"></i></span>
                <span>创建第一个分类</span>
            </a>
        </div>
    </div>
<?php else: ?>
    <!-- 分类列表 -->
    <div class="modern-table">
        <div class="table-container">
            <table class="category-tree-table min-w-full text-sm text-slate-700">
                <thead>
                    <tr>
                        <th style="min-width: 280px;">分类名称</th>
                        <th>别名 (Slug)</th>
                        <th>内容数</th>
                        <th>创建时间</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    global $categoryModel;
                    $categoryModel = new \App\Models\Category();
                    renderCategoryTree($categories, $type, $themeGradient, $icon, $categoryModel, $baseUrl);
                    ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<style>
    .category-tree-table .tree-line {
        opacity: 0.5;
    }

    .category-tree-table tr.level-1 {
        background: rgba(0, 0, 0, 0.01);
    }

    .category-tree-table tr.level-2 {
        background: rgba(0, 0, 0, 0.02);
    }

    .category-tree-table tr.level-3 {
        background: rgba(0, 0, 0, 0.03);
    }
</style>