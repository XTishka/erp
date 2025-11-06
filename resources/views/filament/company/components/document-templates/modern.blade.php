<x-company.document-template.container class="modern-template-container" :preview="$preview">
    <!-- Colored Header with Logo -->
    <x-company.document-template.header class="bg-gray-800 h-24">
        <!-- Logo -->
        <div class="w-2/3">
            @if($document->logo && $document->showLogo)
                <x-company.document-template.logo class="ml-8" :src="$document->logo"/>
            @endif
        </div>

        <!-- Ribbon Container -->
        <div class="w-1/3 absolute right-0 top-0 p-3 h-32 flex flex-col justify-end rounded-bl-sm"
             style="background: {{ $document->accentColor }};">
            @if($document->header)
                <h1 class="text-4xl font-bold text-white text-center uppercase">{{ $document->header }}</h1>
            @endif
        </div>
    </x-company.document-template.header>

    <!-- Company Details -->
    <x-company.document-template.metadata class="modern-template-metadata space-y-8">
        <div class="text-sm">
            <strong class="text-sm block">{{ $document->company->name }}</strong>
            @if($formattedAddress = $document->company->getFormattedAddressHtml())
                {!! $formattedAddress !!}
            @endif
        </div>

        <div class="flex justify-between items-end">
            <!-- Billing Details -->
            <div class="text-sm">
                <h3 class="text-gray-600 font-medium mb-1">BILL TO</h3>
                <p class="text-sm font-bold"
                   style="color: {{ $document->accentColor }}">{{ $document->client?->name ?? 'Client Not Found' }}</p>

                @if($document->client && ($formattedAddress = $document->client->getFormattedAddressHtml()))
                    {!! $formattedAddress !!}
                @endif
            </div>

            <div class="text-sm">
                <table class="min-w-full">
                    <tbody>
                    <tr>
                        <td class="font-semibold text-right pr-2">{{ $document->label->number }}:</td>
                        <td class="text-left pl-2">{{ $document->number }}</td>
                    </tr>
                    @if($document->referenceNumber)
                        <tr>
                            <td class="font-semibold text-right pr-2">{{ $document->label->referenceNumber }}:</td>
                            <td class="text-left pl-2">{{ $document->referenceNumber }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td class="font-semibold text-right pr-2">{{ $document->label->date }}:</td>
                        <td class="text-left pl-2">{{ $document->date }}</td>
                    </tr>
                    <tr>
                        <td class="font-semibold text-right pr-2">{{ $document->label->dueDate }}:</td>
                        <td class="text-left pl-2">{{ $document->dueDate }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </x-company.document-template.metadata>

    <!-- Line Items Table -->
    <x-company.document-template.line-items class="modern-template-line-items">
        <table class="w-full text-left table-fixed">
            <thead class="text-sm leading-relaxed">
            <tr class="text-gray-600">
                <th class="text-left pl-6 w-[50%] py-4">{{ $document->columnLabel->items }}</th>
                <th class="text-center w-[10%] py-4">{{ $document->columnLabel->units }}</th>
                <th class="text-right w-[20%] py-4">{{ $document->columnLabel->price }}</th>
                <th class="text-right pr-6 w-[20%] py-4">{{ $document->columnLabel->amount }}</th>
            </tr>
            </thead>
            <tbody class="text-sm border-y-2">
            @foreach($document->lineItems as $index => $item)
                <tr @class(['bg-gray-100' => $index % 2 === 0])>
                    <td class="text-left pl-6 font-semibold py-3">
                        {{ $item->name }}
                        @if($item->description)
                            <div class="text-gray-600 font-normal line-clamp-2 mt-1">{{ $item->description }}</div>
                        @endif
                    </td>
                    <td class="text-center py-3">{{ $item->quantity }}</td>
                    <td class="text-right py-3">{{ $item->unitPrice }}</td>
                    <td class="text-right pr-6 py-3">{{ $item->subtotal }}</td>
                </tr>
            @endforeach
            </tbody>
            <tfoot class="text-sm summary-section">
            @if($document->subtotal)
                <tr>
                    <td class="pl-6 py-2" colspan="2"></td>
                    <td class="text-right font-semibold py-2">Subtotal:</td>
                    <td class="text-right pr-6 py-2">{{ $document->subtotal }}</td>
                </tr>
            @endif
            @if($document->discount)
                <tr class="text-success-800">
                    <td class="pl-6 py-2" colspan="2"></td>
                    <td class="text-right py-2">Discount:</td>
                    <td class="text-right pr-6 py-2">
                        ({{ $document->discount }})
                    </td>
                </tr>
            @endif
            @if($document->tax)
                <tr>
                    <td class="pl-6 py-2" colspan="2"></td>
                    <td class="text-right py-2">Tax:</td>
                    <td class="text-right pr-6 py-2">{{ $document->tax }}</td>
                </tr>
            @endif
            <tr>
                <td class="pl-6 py-2" colspan="2"></td>
                <td class="text-right font-semibold border-t py-2">{{ $document->amountDue ? 'Total' : 'Grand Total' }}:</td>
                <td class="text-right border-t pr-6 py-2">{{ $document->total }}</td>
            </tr>
            @if($document->amountDue)
                <tr>
                    <td class="pl-6 py-2" colspan="2"></td>
                    <td class="text-right font-semibold border-t-4 border-double py-2">{{ $document->label->amountDue }}
                        ({{ $document->currencyCode }}):
                    </td>
                    <td class="text-right border-t-4 border-double pr-6 py-2">{{ $document->amountDue }}</td>
                </tr>
            @endif
            </tfoot>
        </table>
    </x-company.document-template.line-items>

    <!-- Footer Notes -->
    <x-company.document-template.footer class="modern-template-footer">
        <h4 class="font-semibold px-6 text-sm" style="color: {{ $document->accentColor }}">
            Terms & Conditions
        </h4>
        <span class="border-t-2 my-2 border-gray-300 block w-full"></span>
        <div class="flex flex-col gap-4 px-6 text-sm md:flex-row md:space-x-6">
            <div class="md:w-1/2">
                <p class="break-words line-clamp-4 whitespace-pre-line">{{ $document->terms }}</p>
            </div>

            <div class="md:w-1/2 space-y-3">
                @if(!empty($document->paymentDetails))
                    <div>
                        <h5 class="font-semibold text-gray-600">Payment Details</h5>
                        <dl class="space-y-1">
                            @foreach($document->paymentDetails as $detail)
                                <div class="flex items-start justify-between gap-4">
                                    <dt class="font-semibold text-gray-500">{{ $detail->label }}</dt>
                                    <dd class="text-right break-words text-gray-900">{{ $detail->value }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif

                @if($document->footer)
                    <p class="break-words line-clamp-4">{{ $document->footer }}</p>
                @endif
            </div>
        </div>
    </x-company.document-template.footer>
</x-company.document-template.container>
