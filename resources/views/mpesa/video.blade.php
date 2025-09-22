@include('components.mpesa-payment', [
    'pageTitle' => 'Pay for Video',
    'paymentTitle' => 'M-Pesa Video Payment',
    'content' => $video,
    'type' => 'video',
    'showPreview' => true,
    'cancelRoute' => route('videos.index')
])