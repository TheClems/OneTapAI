<?php
require_once 'config.php';
requireLogin();

$user = getCurrentUser();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account - OneTapAI</title>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lucide/0.263.1/umd/lucide.js"></script>
    <link rel="stylesheet" href="css/plans.css">
    <link rel="stylesheet" href="css/animations.css">
</head>
<body class="body_account">
    <?php require_once 'nav.php'; ?>

    <div class="animated-bg" id="animatedBg"></div>

    <div class="main-content">
        
        <h1>Plans</h1>
        <p class="subtitle">Choose the right plan for you</p>

        <div
            class="tw-mt-10 tw-flex tw-flex-wrap tw-place-content-center tw-gap-8 max-lg:tw-flex-col">
            <div
                class="reveal-up tw-flex tw-w-[350px] tw-flex-col tw-place-items-center tw-gap-2 tw-rounded-lg tw-border-[1px]
                            tw-border-outlineColor tw-bg-white dark:tw-bg-[#080808] dark:tw-border-[#1f2123] tw-p-8 tw-shadow-xl max-lg:tw-w-[320px]">
                <h3 class="">
                    <span class="tw-text-5xl max-md:tw-text-3xl tw-font-semibold">$9</span>
                    <span class="tw-text-2xl tw-text-gray-600 dark:tw-text-gray-300">/mo</span>
                </h3>
                <p class="tw-mt-3 tw-text-center tw-text-gray-800 dark:tw-text-gray-100">
                    Essential AI tools for everyday use
                </p>
                <hr />
                <ul
                    class="tw-mt-4 tw-flex tw-flex-col tw-gap-4 tw-text-base tw-text-gray-800 dark:tw-text-gray-200">
                    <li class="tw-flex tw-gap-2">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>1,000 AI powered chat messages</span>
                    </li>
                    <li class="tw-flex tw-gap-2">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>30 premium image generations</span>
                    </li>
                    <li class="tw-flex tw-gap-2">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>10 premium music generation</span>
                    </li>

                    <li class="tw-flex tw-gap-2 ">
                        <i class="bi bi-check-circle-fill tw-text-gray-400 dark:tw-text-gray-500"></i>
                        <span>Access to all premium AI models</span>
                    </li>

                    <li class="tw-flex tw-gap-2 ">
                        <i class="bi bi-check-circle-fill tw-text-gray-400 dark:tw-text-gray-500"></i>
                        <span>Early access to new features</span>
                    </li>

                </ul>
                <a
                    href="#"
                    class="btn tw-mt-auto !tw-w-full tw-transition-transform tw-duration-[0.3s] 
                                hover:tw-scale-x-[1.02] !tw-text-black !tw-bg-transparent !tw-border-[1px]
                                 tw-border-black dark:tw-border-white dark:!tw-text-white">
                    Choose plan
                </a>
            </div>

            <div
                class="reveal-up tw-flex tw-w-[350px] tw-flex-col tw-place-items-center tw-gap-2 tw-rounded-lg tw-border-2
                            tw-border-outlineColor tw-bg-white dark:tw-bg-[#080808] dark:tw-border-[#595858] tw-p-8 tw-shadow-xl max-lg:tw-w-[320px]">
                <h3 class="">
                    <span class="tw-text-5xl max-md:tw-text-3xl  tw-font-semibold">$17</span>
                    <span class="tw-text-2xl max-md:tw-text-xl  tw-text-gray-600 dark:tw-text-gray-300">/mo</span>
                </h3>
                <p class="tw-mt-3 tw-text-center tw-text-gray-800 dark:tw-text-gray-200">
                    Advanced features for serious AI enthusiasts.
                </p>
                <hr />
                <ul
                    class="tw-mt-4 tw-flex tw-flex-col tw-gap-4  tw-text-base tw-text-gray-800 dark:tw-text-gray-100">
                    <li class="tw-flex tw-gap-2">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>5,000 AI powered chat messages</span>
                    </li>
                    <li class="tw-flex tw-gap-2">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>100 premium image generations</span>
                    </li>
                    <li class="tw-flex tw-gap-2">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>40 premium music generation</span>
                    </li>

                    <li class="tw-flex tw-gap-2 ">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Access to all premium AI models</span>
                    </li>

                    <li class="tw-flex tw-gap-2 ">
                        <i class="bi bi-check-circle-fill tw-text-gray-400 dark:tw-text-gray-500"></i>
                        <span>Early access to new features</span>
                    </li>

                </ul>
                <a
                    href="#"
                    class="btn tw-mt-auto !tw-w-full tw-transition-transform tw-duration-[0.3s] hover:tw-scale-x-[1.02]">
                    Choose plan
                </a>
            </div>

            <div
                class="reveal-up tw-flex tw-w-[350px] tw-flex-col tw-place-items-center tw-gap-2 tw-rounded-lg tw-border-[1px]
                            tw-border-outlineColor dark:tw-bg-[#080808] dark:tw-border-[#1f2123] tw-bg-white tw-p-8 tw-shadow-xl max-lg:tw-w-[320px]">
                <h3 class="">
                    <span class="tw-text-5xl max-md:tw-text-3xl tw-font-semibold">$29</span>
                    <span class="tw-text-2xl tw-text-gray-600 dark:tw-text-gray-300">/mo</span>
                </h3>
                <p class="tw-mt-3 tw-text-center tw-text-gray-800 dark:tw-text-gray-100">
                    Unlimited potential for power users
                </p>
                <hr />
                <ul
                    class="tw-mt-4 tw-flex tw-flex-col tw-gap-4 tw-text-base 
                                tw-text-gray-800 dark:tw-text-gray-200">
                    <li class="tw-flex tw-gap-2">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>10,000 AI powered chat messages</span>
                    </li>
                    <li class="tw-flex tw-gap-2">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>300 premium image generations</span>
                    </li>
                    <li class="tw-flex tw-gap-2">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>100 premium music generations</span>
                    </li>

                    <li class="tw-flex tw-gap-2 ">
                        <i class="bi bi-check-circle-fill"></i>
                        <span>Access to all premium AI models</span>
                    </li>

                    <li class="tw-flex tw-gap-2 ">
                        <i class="bi bi-check-circle-fill "></i>
                        <span>Early access to new features</span>
                    </li>

                </ul>
                <a
                    href="#"
                    class="btn tw-mt-8 !tw-w-full tw-transition-transform tw-duration-[0.3s] 
                                hover:tw-scale-x-[1.02] !tw-text-black !tw-bg-transparent 
                                !tw-border-[1px] tw-border-black dark:tw-border-white dark:!tw-text-white">
                    Choose plan
                </a>
            </div>

        </div>

    </div>
    <script type="text/javascript" src="scripts/nav.js"></script>
    <script type="text/javascript" src="scripts/animated-bg.js"></script>
</body>
</html>