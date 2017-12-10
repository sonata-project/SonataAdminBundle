<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\AdminBundle\Command;

use Sensio\Bundle\GeneratorBundle\Command\Helper\QuestionHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;

abstract class QuestionableCommand extends ContainerAwareCommand
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
