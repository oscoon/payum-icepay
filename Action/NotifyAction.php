<?php
namespace Payum\Icepay\Action;

use JMS\Serializer\SerializerBuilder;
use Payum\Core\Bridge\Spl\ArrayObject;
use Payum\Core\Bridge\Symfony\ReplyToSymfonyResponseConverter;
use Payum\Core\Exception\RequestNotSupportedException;
use Payum\Core\GatewayAwareTrait;
use Payum\Core\Request\Notify;
use Payum\Icepay\Action\Api\BaseApiAwareAction;
use Payum\Icepay\Reply\NotifyReply;
use Payum\Icepay\Response\GetPaymentResponse;

class NotifyAction extends BaseApiAwareAction
{
    use GatewayAwareTrait;

    /**
     * {@inheritDoc}
     *
     * @param Notify $request
     */
    public function execute($request)
    {
        RequestNotSupportedException::assertSupports($this, $request);

        $model = ArrayObject::ensureArrayObject($request->getModel());

        if ($model->get('PaymentID')) {
            $response = SerializerBuilder::create()->build()->deserialize(
                json_encode($this->api->payment->getPayment(['PaymentID' => $model->get('PaymentID')])),
                GetPaymentResponse::class,
                'json'
            );

            $model['getPaymentResponse'] = $response;

            throw new NotifyReply($model);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supports($request)
    {
        return
            $request instanceof Notify &&
            $request->getModel() instanceof \ArrayAccess
        ;
    }
}
