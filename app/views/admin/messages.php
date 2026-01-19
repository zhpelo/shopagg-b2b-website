<h1 class="title is-3">留言列表</h1>
<div class="box admin-card">
    <table class="table is-fullwidth is-striped">
        <thead>
            <tr>
                <th>姓名</th>
                <th>邮箱</th>
                <th>公司</th>
                <th>内容</th>
                <th>时间</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($messages as $row): ?>
            <tr>
                <td><?= h($row['name']) ?></td>
                <td><?= h($row['email']) ?></td>
                <td><?= h($row['company']) ?></td>
                <td><?= h($row['message']) ?></td>
                <td><?= h($row['created_at']) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

