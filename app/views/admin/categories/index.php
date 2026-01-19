<div class="level">
    <div class="level-left"><h1 class="title is-3">产品分类</h1></div>
    <div class="level-right"><a class="button is-link" href="/admin/categories/create">新建</a></div>
</div>
<div class="box admin-card">
    <table class="table is-fullwidth is-striped">
        <thead>
            <tr>
                <th>名称</th>
                <th>别名</th>
                <th>创建时间</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $row): ?>
            <tr>
                <td><?= h($row['name']) ?></td>
                <td><?= h($row['slug']) ?></td>
                <td><?= h($row['created_at']) ?></td>
                <td>
                    <a class="button is-small is-light" href="/admin/categories/edit?id=<?= (int)$row['id'] ?>">编辑</a>
                    <a class="button is-small is-danger is-light" href="/admin/categories/delete?id=<?= (int)$row['id'] ?>" onclick="return confirm('确认删除？')">删除</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

