<?php
declare(strict_types=1);

namespace Amasty\Affiliate\Setup\Patch\Data;

use Magento\Framework\App\State;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\PageFactory;

class CreateCmsPages implements DataPatchInterface
{

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var State
     */
    private $appState;

    public function __construct(
        PageFactory $pageFactory,
        State $appState
    ) {
        $this->pageFactory = $pageFactory;
        $this->appState = $appState;
    }

    public function apply(): void
    {
        $this->appState->emulateAreaCode('adminhtml', [$this, 'createCmsPages']);
    }

    public function getAliases(): array
    {
        return [];
    }

    public static function getDependencies(): array
    {
        return [];
    }

    public function createCmsPages(): void
    {
        $affilateProgramContent =
            <<<EOD
<p>Promote <a href="{{store url=""}}">{{store url=""}}</a> on your website or via email and social media and get paid!
    Our program is absolutely free to join, easy to sign-up and doesn’t require any technical knowledge.</p>
<p><b>How It Works</b><br>
    Once you become a member of the program, you are supplied with a pack of banners,
    text links and other marketing materials that you can promote wherever you like.
    When users click on one of the links you placed,
    they will be directed to our online store and their activity will be tracked by our affiliate program.
    Each time such users complete a purchase, you earn commission!</p>
<p><b>Real-Time Statistics</b><br>
    The program membership ensures a twenty-for-hour access to your personal account equipped with real-time stats.
    Check your sales, traffic, account balance and see how your banners are performing.</p>
<p><b>Affiliate Account</b><br>
    Please create an account at our site and navigate to your account area once logged in. 
    Then please accept the Terms & Conditions on the Affiliate Settings tab to participate in the affiliate programs.
EOD;

        /** @var \Magento\Cms\Model\Page $affiliateProgramPage */
        $affiliateProgramPage = $this->pageFactory->create()->load('amasty-affiliate-program', 'identifier');
        if (!$affiliateProgramPage->getData()) {
            $affiliateProgramPage
                ->setContentHeading('Welcome to our affiliate program')
                ->setTitle('Welcome to our affiliate program')
                ->setIdentifier('amasty-affiliate-program')
                ->setIsActive(true)
                ->setPageLayout('1column')
                ->setStores([0])
                ->setContent($affilateProgramContent)
                ->save();
        }

        /** @var \Magento\Cms\Model\Page $affiliateFaqPage */
        $affiliateFaqPage = $this->pageFactory->create()->load('amasty-affiliate-faq', 'identifier');
        if (!$affiliateFaqPage->getData()) {
            $affilateFaqContent =
                <<<EOD
<p><b>What and Affiliate Program? How does it work?</b><br>
    An Affiliate Program is a revenue sharing program where the affiliate drives traffic to a merchant's web site in
    exchange for referral commissions. When a user clicks on the affiliate link to the merchant’s website and completes
    a purchase, he (she) gets a commission!</p>

<p><b>How do I get started?</b><br>
    <b>Step 1.</b> Sign up to the program.<br>
    <b>Step 2.</b> Accept the Terms & conditions. Once your application is approved, 
    you will get access to the banners, coupon codes and text links that you can place on your website.<br>
    <b>Step 3.</b> Start collecting your commissions!</p>

<p><b>How much the participation cost?</b>
    The membership in our Affiliate Program is absolutely free. As an active participant you just need to keep linking
    and collect your commissions.</p>

<p><b>Who can participate in the Affiliate Program?</b>
    Anyone! It doesn’t matter whether you run a large ecommerce website or a small blog you are welcome to join our
    program.</p>

<p><b>How are sales tracked?</b>
    Once your membership is approved, you will be provided with banners, text links and other marketing materials that
    you can promote on your website. When users click on one of the links you placed, they will be directed to our site
    and their activity will be tracked by our affiliate program.</p>

<p><b>How much will I earn?</b>
    All affiliates can get a commission from each purchase made by their referrals.<br>
    We pay commissions upon your withdrawal request and when the commission balance is sufficient.</p>
EOD;

            $affiliateFaqPage
                ->setContentHeading('Affiliate Program FAQ')
                ->setTitle('Affiliate Program FAQ')
                ->setIdentifier('amasty-affiliate-faq')
                ->setIsActive(true)
                ->setPageLayout('1column')
                ->setStores([0])
                ->setContent($affilateFaqContent)
                ->save();
        }

        /** @var \Magento\Cms\Model\Page $affiliateConditionsPage */
        $affiliateConditionsPage = $this->pageFactory->create()->load('amasty-affiliate-conditions', 'identifier');
        if (!$affiliateConditionsPage->getData()) {
            $affiliateProgramPage =
                <<<EOD
<p>Please carefully read the following Terms & Conditions text before running our Affiliate Program. 
If you have any questions, don't hesitate to contact us.</p>
<ol>
    <li><b>Modifications</b>
        We may modify any of the terms and conditions within this Agreement at any time and at
        our sole discretion. These modifications may include, but not limited to changes in the scope of available
        referral fees, fee schedules, payment procedures and Affiliate Program rules.
    </li>
    <li><b>Enrollment</b>
        To enroll in the affiliate program, you must accept the Terms & conditions.
    </li>
    <li><b>Promo</b>
        You will be provided with a special promo promo materials (links, banners, widget and coupon code) to share.
    </li>
    <li><b>Commission</b>
        All affiliates receive a commission from orders placed by affiliate referrals.
    </li>
    <li><b>Termination</b>
        Either <a href="{{store url=""}}">{{store url=""}}</a> or the affiliate may terminate this Agreement 
        at any time, with or without cause. Once the agreement is terminated, you should remove all links to the 
        <a href="{{store url=""}}">{{store url=""}}</a> website as well as all
        <a href="{{store url=""}}">{{store url=""}}</a> trademarks and logos.
    </li>
</ol>
EOD;

            $affiliateConditionsPage
                ->setContentHeading('Affiliate Program Terms & Conditions')
                ->setTitle('Affiliate Program Terms & Conditions')
                ->setIdentifier('amasty-affiliate-conditions')
                ->setIsActive(true)
                ->setPageLayout('1column')
                ->setStores([0])
                ->setContent($affiliateProgramPage)
                ->save();
        }
    }
}
