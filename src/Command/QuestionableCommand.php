<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

abstract class QuestionableCommand extends Command
{
    /**
     * @return mixed
     *
     * @phpstan-template T
     * @phpstan-param callable(string): T $validator
     * @phpstan-return T
     */
    final protected function askAndValidate(
        InputInterface $input,
        OutputInterface $output,
        string $questionText,
        string $default,
        callable $validator,
    ) {
        $questionHelper = $this->getQuestionHelper();

        $question = new Question($questionText, $default);

        $question->setValidator($validator);

        return $questionHelper->ask($input, $output, $question);
    }

    final protected function askConfirmation(
        InputInterface $input,
        OutputInterface $output,
        string $questionText,
        string $default,
    ): bool {
        $questionHelper = $this->getQuestionHelper();
        $question = new ConfirmationQuestion(
            (new Question($questionText, $default))->getQuestion(),
            'no' !== $default
        );

        return $questionHelper->ask($input, $output, $question);
    }

    /**
     * @psalm-suppress LessSpecificReturnStatement, MoreSpecificReturnType
     */
    final protected function getQuestionHelper(): QuestionHelper
    {
        return $this->getHelper('question');
    }
}
