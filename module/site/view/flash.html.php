<? $this->begin('flash') ?>

    <? foreach ($this->get('flash')->popMessages('error') as $message) : ?>

        <div class="alert alert-error">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong>Błąd!</strong> <?= $message ?>
        </div>

    <? endforeach ?>

    <? foreach ($this->get('flash')->popMessages('warning') as $message) : ?>

        <div class="alert alert-warning">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong>Uwaga!</strong> <?= $message ?>
        </div>

    <? endforeach ?>

    <? foreach ($this->get('flash')->popMessages('success') as $message) : ?>

        <div class="alert alert-success">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong>OK!</strong> <?= $message ?>
        </div>

    <? endforeach ?>

    <? foreach ($this->get('flash')->popMessages('info') as $message) : ?>

        <div class="alert alert-info">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            <strong>Info!</strong> <?= $message ?>
        </div>

    <? endforeach ?>

<? $this->end() ?>
