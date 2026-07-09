<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" dir="<?php echo e(app()->getLocale() === 'ar' ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?php echo $__env->yieldContent('code'); ?> - <?php echo $__env->yieldContent('title', config('app.name', 'RCA Booking')); ?></title>

    <style>
        :root {
            --brand: #16a34a;
            --brand-dark: #15803d;
        }

        * { box-sizing: border-box; }

        html, body {
            height: 100%;
            margin: 0;
        }

        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1.5rem;
            background: #f4f6f5;
            color: #1f2933;
            font-family: <?php echo e(app()->getLocale() === 'ar' ? "'Almarai'," : ''); ?> ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif;
        }

        <?php if(app()->getLocale() === 'ar'): ?>
        @font-face {
            font-family: 'Almarai';
            src: url('<?php echo e(asset('fonts/almarai/Almarai-Regular.ttf')); ?>') format('truetype');
            font-weight: 400;
            font-display: swap;
        }
        <?php endif; ?>

        .card {
            width: 100%;
            max-width: 30rem;
            background: #fff;
            border-radius: 1rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.06);
            padding: 2.5rem 2rem;
            text-align: center;
        }

        .logo {
            height: 2.75rem;
            margin: 0 auto 1.75rem;
            display: block;
        }

        .code {
            font-size: 3.5rem;
            font-weight: 700;
            line-height: 1;
            color: var(--brand);
            margin: 0 0 0.5rem;
        }

        .heading {
            font-size: 1.25rem;
            font-weight: 600;
            margin: 0 0 0.5rem;
            color: #111827;
        }

        .description {
            font-size: 0.95rem;
            color: #6b7280;
            margin: 0 0 1.75rem;
            line-height: 1.6;
        }

        .actions {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 0.6rem 1.5rem;
            border-radius: 0.5rem;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: background-color 0.15s ease;
        }

        .btn-primary {
            background: var(--brand);
            color: #fff;
        }

        .btn-primary:hover {
            background: var(--brand-dark);
        }

        .btn-secondary {
            background: #f3f4f6;
            color: #374151;
        }

        .btn-secondary:hover {
            background: #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="card">
        <img src="<?php echo e(asset('storage/images/horizontalLogo-02.svg')); ?>" alt="<?php echo e(config('app.name', 'RCA Booking')); ?>" class="logo">

        <p class="code"><?php echo $__env->yieldContent('code'); ?></p>
        <h1 class="heading"><?php echo $__env->yieldContent('heading'); ?></h1>
        <p class="description"><?php echo $__env->yieldContent('description'); ?></p>

        <div class="actions">
            <?php echo $__env->yieldContent('actions'); ?>
        </div>
    </div>
</body>
</html>
<?php /**PATH /Users/majidalhajri/Downloads/booking/bookingrca/bookingsrca/resources/views/errors/layout.blade.php ENDPATH**/ ?>