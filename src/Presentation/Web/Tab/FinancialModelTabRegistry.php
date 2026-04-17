<?php

declare(strict_types=1);

namespace App\Presentation\Web\Tab;

final class FinancialModelTabRegistry
{
    /** @var array<string, FinancialModelTabDefinition>|null */
    private ?array $definitions = null;

    /**
     * @param iterable<FinancialModelTabProviderInterface> $providers
     */
    public function __construct(
        private readonly iterable $providers,
    ) {
    }

    /**
     * @return FinancialModelTabDefinition[]
     */
    public function all(): array
    {
        $definitions = $this->definitions();

        usort(
            $definitions,
            static fn (FinancialModelTabDefinition $left, FinancialModelTabDefinition $right): int => $left->order <=> $right->order,
        );

        return $definitions;
    }

    public function get(string $key): FinancialModelTabDefinition
    {
        $definitions = $this->definitions();

        if (!isset($definitions[$key])) {
            throw new \InvalidArgumentException(sprintf('Вкладка финансовой модели "%s" не зарегистрирована.', $key));
        }

        return $definitions[$key];
    }

    /**
     * @return array<string, FinancialModelTabDefinition>
     */
    private function definitions(): array
    {
        if ($this->definitions !== null) {
            return $this->definitions;
        }

        $definitions = [];
        foreach ($this->providers as $provider) {
            $definition = $provider->getDefinition();

            if (isset($definitions[$definition->key])) {
                throw new \LogicException(sprintf('Вкладка финансовой модели "%s" зарегистрирована повторно.', $definition->key));
            }

            $definitions[$definition->key] = $definition;
        }

        return $this->definitions = $definitions;
    }
}
