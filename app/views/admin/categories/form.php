<!-- 页面头部 -->
<div class="page-header animate-in" style="background: linear-gradient(135deg, #ffc107 0%, #fd7e14 100%); box-shadow: 0 10px 40px rgba(255, 193, 7, 0.3);">
    <div class="level mb-0">
        <div class="level-left">
            <div>
                <h1 class="title is-4 mb-1">
                    <span class="icon mr-2"><i class="fas fa-<?= isset($category) ? 'edit' : 'plus' ?>"></i></span>
                    <?= isset($category) ? '编辑分类' : '新建分类' ?>
                </h1>
                <p class="subtitle is-6"><?= isset($category) ? '修改分类信息' : '创建新的产品分类' ?></p>
            </div>
        </div>
        <div class="level-right header-actions">
            <a href="/admin/categories" class="button is-white">
                <span class="icon"><i class="fas fa-arrow-left"></i></span>
                <span>返回列表</span>
            </a>
        </div>
    </div>
</div>

<div class="columns">
    <div class="column is-6 animate-in delay-1">
        <form method="post" action="<?= h($action) ?>" class="modern-form">
            <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
            
            <div class="admin-card" style="padding: 2rem;">
                <div class="section-title">
                    <span class="icon-box warning"><i class="fas fa-folder"></i></span>
                    分类信息
                </div>
                
                <div class="field">
                    <label class="label">分类名称</label>
                    <div class="control has-icons-left">
                        <input class="input" name="name" value="<?= h($category['name'] ?? '') ?>" required placeholder="输入分类名称">
                        <span class="icon is-left has-text-grey-light">
                            <i class="fas fa-tag"></i>
                        </span>
                    </div>
                    <p class="help has-text-grey">分类的显示名称</p>
                </div>
                
                <div class="field">
                    <label class="label">别名 (Slug)</label>
                    <div class="control has-icons-left">
                        <input class="input" name="slug" value="<?= h($category['slug'] ?? '') ?>" placeholder="category-slug">
                        <span class="icon is-left has-text-grey-light">
                            <i class="fas fa-link"></i>
                        </span>
                    </div>
                    <p class="help has-text-grey">用于URL的标识符，留空则自动生成</p>
                </div>

                <hr style="margin: 1.5rem 0;">
                
                <div class="buttons">
                    <button type="submit" class="button is-warning">
                        <span class="icon"><i class="fas fa-save"></i></span>
                        <span><?= isset($category) ? '保存修改' : '创建分类' ?></span>
                    </button>
                    <a href="/admin/categories" class="button is-light">
                        <span class="icon"><i class="fas fa-times"></i></span>
                        <span>取消</span>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
