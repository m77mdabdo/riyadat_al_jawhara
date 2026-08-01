<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class SettingSeeder extends Seeder
{
    public function run(): void
    {
        Setting::updateOrCreate(
            ['id' => 1],
            [
                'phone' => '+966 55 123 4567',
                'whatsapp' => '966551234567',
                'email' => 'info@jra-stone.sa',
                'address_ar' => 'طريق الملك فهد، حي العليا، الرياض، المملكة العربية السعودية',
                'address_en' => 'King Fahd Road, Al Olaya District, Riyadh, Saudi Arabia',
                'facebook_url' => 'https://facebook.com/jra.stone',
                'instagram_url' => 'https://instagram.com/jra.stone',
                'tiktok_url' => 'https://tiktok.com/@jra.stone',
                'snapchat_url' => 'https://snapchat.com/add/jra.stone',
                'working_hours_ar' => 'السبت - الخميس: 9 صباحًا - 6 مساءً',
                'working_hours_en' => 'Sat - Thu: 9:00 AM - 6:00 PM',
                'about_ar' => 'ريادة الجوهرة الإبداعية (JRA) شركة متخصصة في توريد وتركيب وتصميم الأحجار الطبيعية والرخام، نخدم عملاءنا في جميع أنحاء المملكة العربية السعودية بأعلى معايير الجودة والدقة والإبداع.',
                'about_en' => 'Riyadat Al-Jawhara Al-Ibdaeya (JRA) is a company specialized in the supply, installation, and design of natural stone and marble, serving clients across Saudi Arabia with the highest standards of quality, precision, and creativity.',
                'vision_ar' => 'أن نكون الخيار الأول في المملكة لأعمال الحجر والرخام الفاخرة.',
                'vision_en' => 'To be the first choice in the Kingdom for premium stone and marble work.',
                'mission_ar' => 'تقديم حلول متكاملة من التوريد إلى التركيب والتصميم بجودة عالية والتزام تام بالمواعيد.',
                'mission_en' => 'Delivering end-to-end solutions from supply to installation and design, with high quality and strict adherence to deadlines.',
                'map_lat' => 24.7136,
                'map_lng' => 46.6753,
                'privacy_policy_en' => $this->privacyPolicyEn(),
                'privacy_policy_ar' => $this->privacyPolicyAr(),
                'terms_en' => $this->termsEn(),
                'terms_ar' => $this->termsAr(),
            ]
        );
    }

    private function privacyPolicyEn(): string
    {
        return <<<'HTML'
        <p>Riyadat Al-Jawhara Al-Ibdaeya ("JRA", "we", "us") respects your privacy. This page explains what personal information we collect through this website, why we collect it, and how you can ask us to have it removed.</p>

        <h2>What we collect</h2>
        <ul>
            <li><strong>Quote request form:</strong> your name, phone number, and the email address, city, service, stone type, and message you choose to provide.</li>
            <li><strong>Blog comments:</strong> the name and email address you enter, along with your comment text. We also record the IP address and browser information of anyone who submits a comment, used only to detect spam and abusive submissions.</li>
        </ul>

        <h2>Why we collect it</h2>
        <ul>
            <li>To respond to your quote request and follow up with you about your project.</li>
            <li>To display your name alongside a blog comment once it has been reviewed and approved. Your email address is never shown publicly.</li>
            <li>To protect the comment section from spam and automated abuse.</li>
        </ul>

        <h2>What we don't do</h2>
        <ul>
            <li>We do not sell, rent, or share your personal information with third parties for marketing purposes.</li>
            <li>We do not use tracking cookies or third-party analytics scripts on this website.</li>
        </ul>

        <h2>How long we keep it</h2>
        <p>Quote request details and comments are kept for as long as necessary to respond to your inquiry or keep an approved comment visible, or until you ask us to remove them.</p>

        <h2>Your rights</h2>
        <p>You can ask us to access, correct, or delete any personal information we hold about you by emailing us at info@jra-stone.sa or calling +966 55 123 4567.</p>
        HTML;
    }

    private function privacyPolicyAr(): string
    {
        return <<<'HTML'
        <p>تحترم رياضة الجوهرة الإبداعية ("JRA"، "نحن") خصوصيتك. توضح هذه الصفحة البيانات الشخصية التي نجمعها من خلال هذا الموقع، وسبب جمعها، وكيف يمكنك طلب حذفها.</p>

        <h2>ما الذي نجمعه</h2>
        <ul>
            <li><strong>نموذج طلب عرض السعر:</strong> اسمك ورقم هاتفك، بالإضافة إلى البريد الإلكتروني والمدينة والخدمة ونوع الحجر والرسالة التي تختار تقديمها.</li>
            <li><strong>تعليقات المدونة:</strong> الاسم والبريد الإلكتروني اللذان تدخلهما، إلى جانب نص تعليقك. كما نسجل عنوان IP ومعلومات المتصفح لأي شخص يرسل تعليقًا، ونستخدم ذلك فقط لاكتشاف الرسائل المزعجة والتعليقات المسيئة.</li>
        </ul>

        <h2>لماذا نجمعها</h2>
        <ul>
            <li>للرد على طلب عرض السعر الخاص بك والتواصل معك بشأن مشروعك.</li>
            <li>لعرض اسمك بجانب تعليقك على المدونة بعد مراجعته والموافقة عليه. لا يظهر بريدك الإلكتروني للعامة أبدًا.</li>
            <li>لحماية قسم التعليقات من الرسائل المزعجة والإساءة الآلية.</li>
        </ul>

        <h2>ما لا نقوم به</h2>
        <ul>
            <li>لا نبيع أو نؤجر أو نشارك بياناتك الشخصية مع أطراف ثالثة لأغراض تسويقية.</li>
            <li>لا نستخدم ملفات تعريف ارتباط للتتبع أو أدوات تحليل من أطراف ثالثة على هذا الموقع.</li>
        </ul>

        <h2>مدة الاحتفاظ بالبيانات</h2>
        <p>نحتفظ ببيانات طلبات عروض الأسعار والتعليقات للمدة اللازمة للرد على استفسارك أو لإبقاء تعليقك المعتمد ظاهرًا، أو إلى أن تطلب منا حذفها.</p>

        <h2>حقوقك</h2>
        <p>يمكنك طلب الوصول إلى بياناتك الشخصية لدينا أو تصحيحها أو حذفها عبر مراسلتنا على info@jra-stone.sa أو الاتصال بنا على +966 55 123 4567.</p>
        HTML;
    }

    private function termsEn(): string
    {
        return <<<'HTML'
        <p>These Terms of Use govern your use of the Riyadat Al-Jawhara Al-Ibdaeya (JRA) website. By using this site, you agree to these terms.</p>

        <h2>Use of this site</h2>
        <p>This website is provided to showcase our services, projects, and stone and marble products, and to let visitors request quotes and leave comments on our blog. You agree to use the quote request form and comment section honestly, and not to submit false, abusive, or unlawful content.</p>

        <h2>Comments</h2>
        <p>All comments are reviewed before they appear publicly. We reserve the right to remove or decline to publish any comment, including spam, abusive language, or content unrelated to the post.</p>

        <h2>Content ownership</h2>
        <p>All text, images, logos, and design on this website belong to JRA unless otherwise credited, and may not be reproduced without our permission. Project and product photos are used to illustrate our work and services.</p>

        <h2>No warranty</h2>
        <p>Information on this site, including services, pricing indications, and project descriptions, is provided for general informational purposes. Actual quotes, timelines, and specifications are confirmed directly with our team.</p>

        <h2>Changes to these terms</h2>
        <p>We may update these terms from time to time. Continued use of the site after changes means you accept the updated terms.</p>

        <h2>Contact</h2>
        <p>Questions about these terms can be sent to info@jra-stone.sa.</p>
        HTML;
    }

    private function termsAr(): string
    {
        return <<<'HTML'
        <p>تحكم شروط الاستخدام هذه استخدامك لموقع رياضة الجوهرة الإبداعية (JRA). باستخدامك لهذا الموقع، فإنك توافق على هذه الشروط.</p>

        <h2>استخدام هذا الموقع</h2>
        <p>يُقدَّم هذا الموقع لعرض خدماتنا ومشاريعنا ومنتجاتنا من الأحجار والرخام، وللسماح للزوار بطلب عروض الأسعار وترك تعليقات على مدونتنا. أنت توافق على استخدام نموذج طلب عرض السعر وقسم التعليقات بأمانة، وعدم إرسال محتوى كاذب أو مسيء أو مخالف للأنظمة.</p>

        <h2>التعليقات</h2>
        <p>تتم مراجعة جميع التعليقات قبل ظهورها للعامة. نحتفظ بالحق في حذف أو رفض نشر أي تعليق، بما في ذلك الرسائل المزعجة أو اللغة المسيئة أو المحتوى غير المرتبط بالمقال.</p>

        <h2>ملكية المحتوى</h2>
        <p>جميع النصوص والصور والشعارات والتصميم في هذا الموقع مملوكة لشركة JRA ما لم يُذكر خلاف ذلك، ولا يجوز إعادة استخدامها دون إذننا. تُستخدم صور المشاريع والمنتجات لتوضيح أعمالنا وخدماتنا.</p>

        <h2>عدم الضمان</h2>
        <p>المعلومات الواردة في هذا الموقع، بما في ذلك الخدمات ومؤشرات الأسعار ووصف المشاريع، مقدمة لأغراض إعلامية عامة. يتم تأكيد عروض الأسعار والجداول الزمنية والمواصفات الفعلية مباشرة مع فريقنا.</p>

        <h2>التغييرات على هذه الشروط</h2>
        <p>قد نقوم بتحديث هذه الشروط من وقت لآخر. استمرارك في استخدام الموقع بعد أي تغييرات يعني موافقتك على الشروط المحدثة.</p>

        <h2>التواصل</h2>
        <p>يمكن إرسال الأسئلة حول هذه الشروط إلى info@jra-stone.sa.</p>
        HTML;
    }
}
