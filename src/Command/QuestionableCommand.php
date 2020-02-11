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
     * @param string   $questionText
     * @param mixed    $default
     * @param callable $validator
     *
     * @return mixed
     */
    final protected function askAndValidate(
        InputInterface $input,
        OutputInterface $output,
        $questionText,
        $default,
        $validator
    ) {
        $questionHelper = $this->getQuestionHelper();

        $question = new Question($questionHelper->getQuestion($questionText, $default), $default);

        $question->setValidator($validator);

        return $questionHelper->ask($input, $output, $question);
    }

    /**
     * @param string $questionText
     * @param string $default
     * @param string $separator
     *
     * @return string
     */
    final protected function askConfirmation(
        InputInterface $input,
        OutputInterface $output,
        $questionText,
        $default,
        $separator
    ) {
        $questionHelper = $this->getQuestionHelper();

        $question = new ConfirmationQuestion($questionHelper->getQuestion(
            $questionText,
            $default,
            $separator
        ), ('no' === $default ? false : true));

        return $questionHelper->ask($input, $output, $question);
    }

    /**
     * @return QuestionHelper
     */
    final protected function getQuestionHelper()
    {
        $questionHelper = $this->getHelper('question');

        if (!$questionHelper instanceof QuestionHelper) {
            $questionHelper = new QuestionHelper();
            $this->getHelperSet()->set($questionHelper);
        }

        return $questionHelper;
    }
}
