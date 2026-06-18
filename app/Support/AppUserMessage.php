<?php

namespace App\Support;

use Illuminate\Database\QueryException;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use RuntimeException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

/**
 * تحويل الأخطاء والرسائل التقنية إلى نصوص عربية مختصرة للمستخدم.
 */
final class AppUserMessage
{
    public const TYPE_ERROR = 'error';

    public const TYPE_WARNING = 'warning';

    public const TYPE_INFO = 'info';

    /**
     * @return array{type: string, title: string, message: string}
     */
    public static function fromThrowable(Throwable $e): array
    {
        $type = self::TYPE_ERROR;

        if ($e instanceof ThrottleRequestsException) {
            return self::pack($type, 'تم إرسال طلبات كثيرة. انتظر قليلاً ثم أعد المحاولة.');
        }

        if ($e instanceof NotFoundHttpException || ($e instanceof RuntimeException && $e->getCode() === 404)) {
            return self::pack($type, self::friendlyHttpMessage($e, 404));
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return self::pack($type, 'طريقة الطلب غير مسموحة لهذه الصفحة.');
        }

        if ($e instanceof HttpExceptionInterface) {
            return self::pack($type, self::friendlyHttpMessage($e, $e->getStatusCode()));
        }

        if ($e instanceof QueryException) {
            return self::pack($type, 'تعذر حفظ أو قراءة البيانات. تحقق من المدخلات وحاول مرة أخرى.');
        }

        $raw = trim($e->getMessage());
        if ($raw !== '' && ! self::isTechnical($raw)) {
            return self::pack($type, $raw);
        }

        return self::pack($type, 'حدث خطأ غير متوقع. يرجى المحاولة مرة أخرى.');
    }

    /**
     * @return array{type: string, title: string, message: string}
     */
    public static function fromText(string $text, string $type = self::TYPE_ERROR): array
    {
        $text = trim($text);
        if ($text === '') {
            return self::pack($type, self::genericMessage($type));
        }

        if (self::isTechnical($text)) {
            return self::pack($type, self::genericMessage($type));
        }

        return self::pack($type, $text);
    }

    /**
     * @param  list<string>  $lines
     * @return array{type: string, title: string, message: string}
     */
    public static function fromLines(array $lines, string $type = self::TYPE_ERROR): array
    {
        $lines = array_values(array_filter(array_map(static fn ($l) => trim((string) $l), $lines)));
        if ($lines === []) {
            return self::pack($type, self::genericMessage($type));
        }

        $message = implode("\n", array_map(
            static fn (string $line) => self::fromText($line, $type)['message'],
            $lines
        ));

        return self::pack($type, $message);
    }

    public static function titleFor(string $type): string
    {
        return match ($type) {
            self::TYPE_WARNING => 'تحذير',
            self::TYPE_INFO => 'تنبيه',
            default => 'خطأ',
        };
    }

    public static function genericMessage(string $type = self::TYPE_ERROR): string
    {
        return match ($type) {
            self::TYPE_WARNING => 'يرجى مراجعة البيانات والمحاولة مرة أخرى.',
            self::TYPE_INFO => 'يرجى الانتباه إلى الملاحظة التالية.',
            default => 'حدث خطأ غير متوقع. يرجى المحاولة مرة أخرى.',
        };
    }

    public static function isTechnical(string $message): bool
    {
        $message = trim($message);
        if ($message === '') {
            return true;
        }

        $patterns = [
            '/SQLSTATE\[/i',
            '/Illuminate\\\\/',
            '/Symfony\\\\/',
            '/PDOException/i',
            '/QueryException/i',
            '/Stack trace/i',
            '/vendor\\\\/',
            '/\.php:\d+/',
            '/::\w+\(/',
            '/Undefined (array key|variable|index)/i',
            '/Call to (a )?member function/i',
            '/Allowed memory size/i',
            '/Maximum execution time/i',
            '/cURL error/i',
            '/Connection refused/i',
            '/GET|POST|PUT|DELETE\s+\//',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $message)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{type: string, title: string, message: string}
     */
    private static function pack(string $type, string $message): array
    {
        return [
            'type' => $type,
            'title' => self::titleFor($type),
            'message' => $message,
        ];
    }

    private static function friendlyHttpMessage(Throwable $e, int $status): string
    {
        $custom = trim($e->getMessage());
        if ($custom !== '' && ! self::isTechnical($custom)) {
            return $custom;
        }

        return match ($status) {
            400 => 'الطلب غير صالح. تحقق من البيانات المدخلة.',
            401 => 'يلزم تسجيل الدخول للمتابعة.',
            403 => 'غير مسموح بتنفيذ هذا الإجراء.',
            404 => 'لم يتم العثور على الصفحة أو البيانات المطلوبة.',
            405 => 'طريقة الطلب غير مسموحة لهذه الصفحة.',
            419 => 'انتهت صلاحية الجلسة. حدّث الصفحة ثم أعد المحاولة.',
            422 => 'البيانات المدخلة غير مكتملة أو غير صحيحة.',
            429 => 'طلبات كثيرة. انتظر قليلاً ثم أعد المحاولة.',
            500 => 'حدث خطأ في الخادم. يرجى المحاولة لاحقاً.',
            503 => 'الخدمة غير متاحة مؤقتاً. حاول لاحقاً.',
            default => 'تعذر إتمام الطلب. يرجى المحاولة مرة أخرى.',
        };
    }
}
